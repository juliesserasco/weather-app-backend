<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ClientBasicRate;
use App\Models\Accounting\ClientSubcontractors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class ClientBasicRateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        Builder::macro('whereLike', function($columns, $search) {
            $this->where(function($query) use ($columns, $search) {
                foreach(Arr::wrap($columns) as $column) {
                    $query->orWhere($column, 'LIKE', "%{$search}%");
                }
            });
        
            return $this;
        });

        $main_db = Config::get('database.connections');
        $sql_schema = $main_db['mysql']['database'];

        $basic_rates = ClientBasicRate::
        when($request->search, function ($query) use ($request) {
            $query->whereLike(['tblm_client_basic_rate.id', 'tblm_client.client_name', 'basic.reg_firstname' ,'basic.reg_lastname'], $request->search);
        })
        ->join('tblm_client_subcon_pers as subcon_pers', 'tblm_client_basic_rate.link_client_subcon_pers' , '=', 'subcon_pers.id')
        ->join('tblm_client', 'subcon_pers.link_client_id' , '=', 'tblm_client.id')
        ->join($sql_schema.'.tblm_client_sub_contractor as subcon', 'subcon_pers.link_subcon_id' , '=', 'subcon.id')
        ->join($sql_schema.'.tblm_b_onboard_actreg_basic as basic', 'subcon.actreg_contractor_id' , '=', 'basic.reg_id')
        ->select('tblm_client_basic_rate.*', 'tblm_client.id AS client_id', 'tblm_client.client_name', DB::Raw("CONCAT(basic.reg_firstname, ' ' ,basic.reg_lastname) AS subcon_name"))
        ->orderBy('id', 'DESC')->paginate($request->limit ? $request->limit : ClientBasicRate::count());
            
        return response()->json([
            'success' => true,
            'data' => $basic_rates,
        ], 200);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Set validation
		$validator = Validator::make($request->all(), [
            'link_client_subcon_pers' => 'required:mysql2.tblm_client_basic_rate',
            'basic_monthly_rate' => 'required:mysql2.tblm_client_basic_rate',
            'effective_date_from' => 'required:mysql2.tblm_client_basic_rate',
            'effective_date_to' => 'required:mysql2.tblm_client_basic_rate',
        ],
        [
            'link_client_subcon_pers.required' => 'The Client is requried.',
            'basic_monthly_rate.required' => 'The Monthly Rate is requried.',
            'effective_date_from.required' => 'The Effective Date From is requried.',
            'effective_date_to.required' => 'The Effective Date To is requried.',
        ]);


        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $basic_rate = ClientBasicRate::create(
            [
                'link_client_subcon_pers' => $request->link_client_subcon_pers,
                'basic_monthly_rate' => $request->basic_monthly_rate,
                'basic_weekly_rate' => $request->basic_weekly_rate,
                'basic_daily_rate' => $request->basic_daily_rate,
                'basic_hourly_rate' => $request->basic_hourly_rate,
                'effective_date_from' => $request->effective_date_from,
                'effective_date_to' => $request->effective_date_to,
                'is_active' => $request->is_active,
                'createdby' => auth()->user()->id,
                'datecreated' => Carbon::now()
            ]
        );
        
        $main_db = Config::get('database.connections');
        $sql_schema = $main_db['mysql']['database'];
        $basic_rate = ClientBasicRate::
        join('tblm_client_subcon_pers as subcon_pers', 'tblm_client_basic_rate.link_client_subcon_pers' , '=', 'subcon_pers.id')
        ->join('tblm_client', 'subcon_pers.link_client_id' , '=', 'tblm_client.id')
        ->join($sql_schema.'.tblm_client_sub_contractor as subcon', 'subcon_pers.link_subcon_id' , '=', 'subcon.id')
        ->join($sql_schema.'.tblm_b_onboard_actreg_basic as basic', 'subcon.actreg_contractor_id' , '=', 'basic.reg_id')
        ->select('tblm_client_basic_rate.*', 'tblm_client.id AS client_id', 'tblm_client.client_name', DB::Raw("CONCAT(basic.reg_firstname, ' ' ,basic.reg_lastname) AS subcon_name"))
        ->orderBy('id', 'DESC')->paginate($request->limit ? $request->limit : ClientBasicRate::count());
            

        return response()->json([
            'success' => true,
            'data' => $basic_rate,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

        ClientBasicRate::where('id', $request->id)->update(
            [
                'link_client_subcon_pers' => $request->link_client_subcon_pers,
                'basic_monthly_rate' => $request->basic_monthly_rate,
                'basic_weekly_rate' => $request->basic_weekly_rate,
                'basic_daily_rate' => $request->basic_daily_rate,
                'basic_hourly_rate' => $request->basic_hourly_rate,
                'effective_date_from' => $request->effective_date_from,
                'effective_date_to' => $request->effective_date_to,
                'is_active' => $request->is_active,
                'modifiedby' => auth()->user()->id,
                'datemodified' => Carbon::now()
            ]
        );

        
        $main_db = Config::get('database.connections');
        $sql_schema = $main_db['mysql']['database'];
        $basic_rate = ClientBasicRate::
        join('tblm_client_subcon_pers as subcon_pers', 'tblm_client_basic_rate.link_client_subcon_pers' , '=', 'subcon_pers.id')
        ->join('tblm_client', 'subcon_pers.link_client_id' , '=', 'tblm_client.id')
        ->join($sql_schema.'.tblm_client_sub_contractor as subcon', 'subcon_pers.link_subcon_id' , '=', 'subcon.id')
        ->join($sql_schema.'.tblm_b_onboard_actreg_basic as basic', 'subcon.actreg_contractor_id' , '=', 'basic.reg_id')
        ->select('tblm_client_basic_rate.*', 'tblm_client.id AS client_id', 'tblm_client.client_name', DB::Raw("CONCAT(basic.reg_firstname, ' ' ,basic.reg_lastname) AS subcon_name"))
        ->find($request->id);
            

        return response()->json([
            'success' => true,
            'data' => $basic_rate,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */    
    public function delete(Request $request)
    {
        ClientBasicRate::wherein('id', $request->id)->delete();

		$basic_rate = ClientBasicRate::orderBy('id', 'desc')->paginate($request->limit);

        return response()->json([
            'success' => true,
            'data' => $basic_rate,
        ], 200);
    }

    public function getSubcon($id) {
        
        $main_db = Config::get('database.connections');
        $sql_schema = $main_db['mysql']['database'];

        $subcontractors = ClientSubcontractors::
        join($sql_schema.'.tblm_client_sub_contractor as subcon', 'tblm_client_subcon_pers.link_subcon_id' , '=', 'subcon.id')
        ->join($sql_schema.'.tblm_b_onboard_actreg_basic as basic', 'subcon.actreg_contractor_id' , '=', 'basic.reg_id')
        ->where('tblm_client_subcon_pers.link_client_id', '=', $id )
        ->select('tblm_client_subcon_pers.id', DB::Raw("CONCAT(basic.reg_firstname, ' ' ,basic.reg_lastname) AS name"))
        ->get();
            
        return response()->json([
            'success' => true,
            'data' => $subcontractors,
        ], 200);

    }
}
