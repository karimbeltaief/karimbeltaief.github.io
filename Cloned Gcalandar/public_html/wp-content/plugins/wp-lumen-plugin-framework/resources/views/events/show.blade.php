@extends('layouts.master')

@section('content')



<h2 class="page-header">Event</h2>

<div class="panel panel-default">
    <div class="panel-heading">
        View Event    </div>

    <div class="panel-body">
                

        <form action="{{ url('/events') }}" method="POST" class="form-horizontal">


                
        <div class="form-group">
            <label for="id" class="col-sm-3 control-label">Id</label>
            <div class="col-sm-6">
                <input type="text" name="id" id="id" class="form-control" value="{{$model['id'] or ''}}" readonly="readonly">
            </div>
        </div>
        
                
        <div class="form-group">
            <label for="calendar_id" class="col-sm-3 control-label">Calendar Id</label>
            <div class="col-sm-6">
                <input type="text" name="calendar_id" id="calendar_id" class="form-control" value="{{$model['calendar_id'] or ''}}" readonly="readonly">
            </div>
        </div>
        
                
        <div class="form-group">
            <label for="google_id" class="col-sm-3 control-label">Google Id</label>
            <div class="col-sm-6">
                <input type="text" name="google_id" id="google_id" class="form-control" value="{{$model['google_id'] or ''}}" readonly="readonly">
            </div>
        </div>
        
                
        <div class="form-group">
            <label for="name" class="col-sm-3 control-label">Name</label>
            <div class="col-sm-6">
                <input type="text" name="name" id="name" class="form-control" value="{{$model['name'] or ''}}" readonly="readonly">
            </div>
        </div>
        
                
        <div class="form-group">
            <label for="description" class="col-sm-3 control-label">Description</label>
            <div class="col-sm-6">
                <input type="text" name="description" id="description" class="form-control" value="{{$model['description'] or ''}}" readonly="readonly">
            </div>
        </div>
        
                
        <div class="form-group">
            <label for="allday" class="col-sm-3 control-label">Allday</label>
            <div class="col-sm-6">
                <input type="text" name="allday" id="allday" class="form-control" value="{{$model['allday'] or ''}}" readonly="readonly">
            </div>
        </div>
        
                
        <div class="form-group">
            <label for="started_at" class="col-sm-3 control-label">Started At</label>
            <div class="col-sm-6">
                <input type="text" name="started_at" id="started_at" class="form-control" value="{{$model['started_at'] or ''}}" readonly="readonly">
            </div>
        </div>
        
                
        <div class="form-group">
            <label for="ended_at" class="col-sm-3 control-label">Ended At</label>
            <div class="col-sm-6">
                <input type="text" name="ended_at" id="ended_at" class="form-control" value="{{$model['ended_at'] or ''}}" readonly="readonly">
            </div>
        </div>
        
                
        <div class="form-group">
            <label for="created_at" class="col-sm-3 control-label">Created At</label>
            <div class="col-sm-6">
                <input type="text" name="created_at" id="created_at" class="form-control" value="{{$model['created_at'] or ''}}" readonly="readonly">
            </div>
        </div>
        
                
        <div class="form-group">
            <label for="updated_at" class="col-sm-3 control-label">Updated At</label>
            <div class="col-sm-6">
                <input type="text" name="updated_at" id="updated_at" class="form-control" value="{{$model['updated_at'] or ''}}" readonly="readonly">
            </div>
        </div>
        
                
        <div class="form-group">
            <label for="hobby_mood_event" class="col-sm-3 control-label">Hobby Mood Event</label>
            <div class="col-sm-6">
                <input type="text" name="hobby_mood_event" id="hobby_mood_event" class="form-control" value="{{$model['hobby_mood_event'] or ''}}" readonly="readonly">
            </div>
        </div>
        
        
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-6">
                <a class="btn btn-default" href="{{ url('/events') }}"><i class="glyphicon glyphicon-chevron-left"></i> Back</a>
            </div>
        </div>


        </form>

    </div>
</div>







@endsection