<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DateTime;
use DateInterval;
use Illuminate\Support\Facades\DB;
use DatePeriod;
use Illuminate\Support\Facades\Redirect;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Gets months in a year.
     * @return array
     */
    public function getMonths($year)
    {
        $fristDate    = (new DateTime($year.'-01-01'))->modify('first day of this month');
        $lastDate      = (new DateTime($year.'-12-01'))->modify('last day of this month');
        $int = DateInterval::createFromDateString('1 month');
        $periods   = new DatePeriod($fristDate, $int, $lastDate);
        $months = array();

        foreach ($periods as $period)
        {
            $months[] = $period->format("F Y");
        }

        return $months;
    }

    /**
     * Get last weekday in a month
     * @param $month
     * @return DateTime
     */
    public function getLastWeekDay($month)
    {
        $now = new DateTime('now');
        $lastDay = $now->modify('last day of'.' '.$month);
        $date = $lastDay->format('l jS F Y');
        $lastDay = $now->modify('last weekday'.' '.$date);

        return $lastDay;
    }

    /**
     * Get the bonus payment for each month
     * @param $month
     * @return string
     */
    public function getBonus($month)
    {
            $now = (new DateTime('now'))->modify('first day'.' '.$month);
            $fifteen = $now->modify('+13 days');
            $dayFormat = $fifteen->format('d-m-Y');
            $lastDay = $fifteen->format('l');

            if($lastDay === "Saturday")
            {
                $weekend = $fifteen->modify('next wednesday');
                $day = $weekend->format('d-m-Y');
                return $day;
            }
            elseif($lastDay === "Sunday")
            {
                $weekend = $fifteen->modify('next wednesday');
                $day = $weekend->format('d-m-Y');
                return $day;
            }
            else
            {
                return $dayFormat;
            }
    }

    /**
     * Get payments dates per month and save it to database
     */
    public function getPayments($year)
    {
        foreach($this->getMonths($year) as $month)
        {
            $bonus = $this->getBonus($month);
            $now = (new DateTime('now'))->modify('last day of'.' '.$month);
            $dayFormat = $now->format('d-m-Y');
            $lastDay = $now->format('l');

            if(($lastDay === "Saturday") || ($lastDay === "Sunday"))
            {
                $weekend = $this->getLastWeekDay($month);
                $lastWeekday = $weekend->format('d-m-Y');
                DB::table('pay_dates')->insert([
                    ['year' => $year, 'date' => $lastWeekday, 'bonus' => $bonus],
                ]);
            }
            else
            {
                DB::table('pay_dates')->insert([
                    ['year' => $year, 'date' => $dayFormat, 'bonus' => $bonus],
                ]);
            }
        }
        unset($month);
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dates = DB::table('pay_dates')->select('year','date', 'bonus')->get();

        return view('home', compact('dates'));

    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $yearGiven = $request->input('year');
        $year = DB::table('pay_dates')->select('year')->where('year', '=', $yearGiven)->first();
        if($year) {
            $this->csv($year);
            return Redirect::to('home');
        } else {
            $newYear = $request->input('year');
            $this->getPayments($newYear);
            $year = DB::table('pay_dates')->select('year')->where('year', '=', $yearGiven)->first();
            $this->csv($year);
            return Redirect::to('home');
        }
    }

    public function csv($year)
    {
        //Getting data from DB
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="demo.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // create a file pointer connected to the output stream
        $file = fopen('php://output', 'w');

        //Set Columns name
        fputcsv($file, array(
            'type','January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ));

        //Getting data from DB
        $data = DB::table('pay_dates')->select('date','bonus')->where('year','=',$year->year)->get();

        $array1[] = ['Payments'];
        $array2[] = ['Bonus'];
        foreach ($data as $value){
            $array1[] = array($value->date);
            $array2[] = array($value->bonus);
        }
        //Merging payments array
        $payments = array();
        foreach ($array1 as $tmp) {
            $payments = array_merge($payments, array_values($tmp));
        }
        //Merging bonus array
        $bonus = array();
        foreach ($array2 as $tmp) {
            $bonus = array_merge($bonus, array_values($tmp));
        }
        //Initiating single array
        $dates = array_merge(array($payments), array($bonus));

        //Copying array to csv file for download
        foreach ($dates as $value){
            fputcsv($file, $value);
        }
        exit();
    }

}
