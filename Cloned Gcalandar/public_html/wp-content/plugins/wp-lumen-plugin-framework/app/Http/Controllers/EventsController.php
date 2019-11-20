<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Helpers\LumenHelper;

use App\Event;

use DB;

class EventsController extends Controller
{
  //
  public function __construct(LumentHelper $helper)
  {
    //$this->middleware('auth');
    $this->helper = $helper;
  }


  public function index(Request $request)
  {
    return $this->helper->view('events', []);
  }

  public function create(Request $request)
  {
    return view('events.add', [
      []
    ]);
  }

  public function edit(Request $request, $id)
  {
    $event = Event::findOrFail($id);
    return view('events.add', [
      'model' => $event    ]);
  }

  public function show(Request $request, $id)
  {
    $event = Event::findOrFail($id);
    return view('events.show', [
      'model' => $event    ]);
  }

  public function grid(Request $request)
  {
    $len = $_GET['length'];
    $start = $_GET['start'];

    $select = "SELECT *,1,2 ";
    $presql = " FROM events a ";
    if($_GET['search']['value']) {
      $presql .= " WHERE calendar_id LIKE '%".$_GET['search']['value']."%' ";
    }

    $presql .= "  ";

    //------------------------------------
    // 1/2/18 - Jasmine Robinson Added Orderby Section for the Grid Results
    //------------------------------------
    $orderby = "";
    $columns = array('id','calendar_id','google_id','name','description','allday','started_at','ended_at','created_at','updated_at','hobby_mood_event',);
    $order = $columns[$request->input('order.0.column')];
    $dir = $request->input('order.0.dir');
    $orderby = "Order By " . $order . " " . $dir;

    $sql = $select.$presql.$orderby." LIMIT ".$start.",".$len;
    //------------------------------------

    $qcount = DB::select("SELECT COUNT(a.id) c".$presql);
    //print_r($qcount);
    $count = $qcount[0]->c;

    $results = DB::select($sql);
    $ret = [];
    foreach ($results as $row) {
      $r = [];
      foreach ($row as $value) {
        $r[] = $value;
      }
      $ret[] = $r;
    }

    $ret['data'] = $ret;
    $ret['recordsTotal'] = $count;
    $ret['iTotalDisplayRecords'] = $count;

    $ret['recordsFiltered'] = count($ret);
    $ret['draw'] = $_GET['draw'];

    echo json_encode($ret);

  }


  public function update(Request $request) {
    //
    /*$this->validate($request, [
    'name' => 'required|max:255',
  ]);*/
  $event = null;
  if($request->id > 0) { $event = Event::findOrFail($request->id); }
  else {
    $event = new Event;
  }


  
    $event->id = $request->id?:0;
    
  
      $event->calendar_id = $request->calendar_id;
  
  
      $event->google_id = $request->google_id;
  
  
      $event->name = $request->name;
  
  
      $event->description = $request->description;
  
  
      $event->allday = $request->allday;
  
  
      $event->started_at = $request->started_at;
  
  
      $event->ended_at = $request->ended_at;
  
  
      $event->created_at = $request->created_at;
  
  
      $event->updated_at = $request->updated_at;
  
  
      $event->hobby_mood_event = $request->hobby_mood_event;
  
    //$event->user_id = $request->user()->id;
  $event->save();

  return redirect('/events');

}

public function store(Request $request)
{
  return $this->update($request);
}

public function destroy(Request $request, $id) {

  $event = Event::findOrFail($id);

  $event->delete();
  return "OK";

}


}
