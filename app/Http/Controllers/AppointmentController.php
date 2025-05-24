<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Google\Service\Calendar;
use Google\Service\Calendar\FreeBusyRequest;
use Google\Service\Calendar\Event as CalendarEvent;
use Google\Service\Calendar\EventAttendee;
use Google\Service\Calendar\EventReminder;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function showForm($id)
    {
        $user = User::where('id', $id)->firstOrFail();
        $schedules = Schedule::where('user_id', $user->id)->get();

        // Gera dias e horários disponíveis
        $available = $this->generateAvailableSlots($user, $schedules);

        return view('schedule.form', compact('user', 'available'));
    }

    public function store(Request $request, $id)
    {
        $user = User::where('id', $id)->firstOrFail();

        $validated = $request->validate([
            'date' => 'required|date',
            'time' => 'required',
            'name' => 'required',
            'email' => 'required|email',
        ]);

        $datetime = Carbon::parse("{$validated['date']} {$validated['time']}");

        // Checa conflito no Google Calendar
        if ($this->hasConflictInGoogleCalendar($user, $datetime)) {
            return back()->with('error', 'Horário indisponível no Google Calendar.');
        }

        // Salva localmente
        // Appointment::create([
        //     'user_id' => $user->id,
        //     'name' => $validated['name'],
        //     'email' => $validated['email'],
        //     'datetime' => $datetime,
        // ]);

        // Cria no Google Calendar
        $this->createGoogleEvent($user, $datetime, ['name' => $validated['name'], 'email' => $validated['email']]);

        return redirect()->back()->with('success', 'Agendamento realizado com sucesso!');
    }

    protected function generateAvailableSlots(User $user, $schedules)
    {
        $available = [];

        foreach ($schedules as $schedule) {
            $dayNum = date('N', strtotime($schedule->day)); // segunda = 1
            $start = Carbon::createFromFormat('H:i:s', $schedule->start_time);
            $end = trim($schedule->end_time);
            $end = Carbon::createFromFormat('H:i:s', $end);
            $interval = abs($end->diffInMinutes($start) / $schedule->records_per_day);

            // Ex: próximos 7 dias
            for ($i = 0; $i < 7; $i++) {
                $date = Carbon::today()->addDays($i);
                if ($date->dayOfWeekIso != $dayNum) continue;

                $slots = [];
                for ($t = $start->copy(); $t->lt($end); $t->addMinutes($interval)) {
                    $slots[] = $t->format('H:i');
                }

                $available[$date->toDateString()] = $slots;
            }
        }

        return $available;
    }

    protected function hasConflictInGoogleCalendar(User $user, Carbon $datetime)
    {
        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->setAccessType('offline');
        $client->setScopes(['https://www.googleapis.com/auth/calendar']);
        $provider = $user->providers()
            ->where('provider', 'google')
            ->first();
        // $provider = Auth::user()->providers()
        //     ->where('provider', 'google')
        //     ->first();
        // $client->setAccessToken($provider->access_token); // Assuma que isso retorna array com tokens válidos
        
        $client->setAccessToken([
            'access_token' => $provider->access_token,
            'refresh_token' => $provider->refresh_token,'1'
        ]);

        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

            $newToken = $client->getAccessToken();
            $provider->access_token = $newToken['access_token'];
            $provider->token_expires_at = Carbon::createFromTimestamp($newToken['expires_in'] + $newToken['created'])->toDateString();
            $provider->save();
        }

        $service = new Calendar($client);

        $body = new FreeBusyRequest([
            'timeMin' => $datetime->copy()->subHour()->toRfc3339String(),
            'timeMax' => $datetime->copy()->addHour()->toRfc3339String(),
            'items' => [['id' => 'primary']],
        ]);

        $results = $service->freebusy->query($body);
        $busy = $results->getCalendars()['primary']['busy'];

        return count($busy) > 0;
    }

    protected function createGoogleEvent(User $user, Carbon $startTime, $summary)
    {
        $client = new Google_Client();
        $provider = $user->providers()->where('provider', 'google')->first();
        $client->setAccessToken($provider->access_token);

        $service = new Calendar($client);
        $calendar = $service->calendars->get('primary');
        $timezone = $calendar->getTimeZone();

        $startTime = $startTime->copy()->setTimezone($timezone);
        $startRfc = $startTime->addHours(3)->toRfc3339String();

        $event = new CalendarEvent([
            'summary' => "Agendamento: {$summary['name']} - {$summary['email']}",
            'start' => ['dateTime' => $startRfc, 'timeZone' => $timezone],
            'end' => ['dateTime' => $startTime->copy()->addMinutes(30)->toRfc3339String(), 'timeZone' => $timezone],
            'attendees' => [
                new EventAttendee([
                    'email' => $summary['email'],
                    'displayName' => $summary['name'],
                    'responseStatus' => 'accepted'
                ])
            ],
            'reminders' => [
                'useDefault' => false,
                'overrides' => [
                    new EventReminder(['method' => 'popup', 'minutes' => 5]),
                    new EventReminder(['method' => 'popup', 'minutes' => 3])
                ]
            ]
        ]);

        return $service->events->insert('primary', $event);
    }
}