<?php

namespace App\Http\Controllers;

use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Schedule;
use App\Models\User;
use App\Models\UserProvider;
use Google_Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Calendar as CalendarT;
use Illuminate\Support\Facades\Auth;

class SignupController extends Controller
{
    public function index()
    {
        $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

        $start = Carbon::createFromTimeString('08:00');
        $end = Carbon::createFromTimeString('18:00');
        $intervalMinutes = 30;

        $period = CarbonPeriod::create($start, $intervalMinutes . ' minutes', $end);
        $times = [];
        foreach($period as $time) {
            $times[] = $time->format('H:i');
        }

        return view('singup/index', [
            'days' => $days,
            'times' => $times
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->post();

        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->setAccessType('offline');
        $client->setScopes(['https://www.googleapis.com/auth/calendar']);

        $provider = Auth::user()->providers()
            ->where('provider', 'google')
            ->first();   

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

        $calendarService = new Calendar($client);

        $calendar = new CalendarT();
        $calendar->setSummary("Agenda do TESTER");
        $calendar->setTimeZone('America/Sao_Paulo');

        $createdCalendar = $calendarService->calendars->insert($calendar);
        $calendarId = $createdCalendar->getId();

        foreach ($data['days'] as $day) {
            $schedule = Schedule::create([
                'day' => $day,
                'start_time' => $data['start'],
                'end_time' => $data['end'],
                'records_per_day' => $data['records_per_day'],
                'user_id' => Auth::user()->id
            ]);

            $schedule->save();
        }

        $provider->calendar_id = $calendarId;
        $provider->save();

        return view('signin/index');
    }
}
