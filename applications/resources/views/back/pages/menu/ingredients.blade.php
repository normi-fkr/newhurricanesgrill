@extends('back.layout.master')

@section('headscript')
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css') }}">
@endsection

@section('breadcrumb')
  <h1>
      Menu Management <small>Ingredients List</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">Ingredients List</li>
  </ol>
@stop

@section('content')

  <div class="modal fade" id="myModalEditIngredient" role="dialog">
    <div class="modal-dialog" style="width:500px;">
      <form class="form-horizontal" action="{{ route('menu.ingredientUpdate') }}" method="post">
        {!! csrf_field() !!}
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Edit Ingredient</h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <div class="{{ $errors->has('editName') ? 'has-error' : '' }}">
                <label class="col-sm-4 control-label">Ingredient Name</label>
              </div>
              <div class="col-sm-8 {{ $errors->has('editName') ? 'has-error' : '' }}">
                <input type="text" name="editName" class="form-control" placeholder="Ingredient Name" id="editName" value="{{ old('editName') }}" />
                @if($errors->has('editName'))
                  <span class="help-block">
                    <i>* {{$errors->first('editName')}}</i>
                  </span>
                @endif
                <input type="hidden" name="editId" id="editId" value="">
                <input type="hidden" name="editUser_id" value="{{ Auth::user()->id }}">
              </div>
            </div>
            <div class="form-group">
              <div class="{{ $errors->has('editUnit') ? 'has-error' : ''}}">
                <label class="col-sm-4 control-label">Ingredient Unit</label>
              </div>
              <div class="col-sm-8 {{ $errors->has('editUnit') ? 'has-error' : ''}}">
                <input type="text" name="editUnit" class="form-control" placeholder="Ex: gr, slice, ea, ml" id="editUnit" value="{{ old('editUnit')}}" />
                @if($errors->has('editUnit'))
                  <span class="help-block">
                    <i>* {{ $errors->first('editUnit')}}</i>
                  </span>
                @endif
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="reset" class="btn btn-default pull-left btn-flat" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary bg-orange">Update Ingredient</button>
          </div>
        </div>
    </form>
    </div>
  </div>

  <div class="modal fade" id="myModalCreateIngredients" role="dialog">
    <div class="modal-dialog" style="width:500px;">
      <form class="form-horizontal" action="{{ route('menu.ingredientCreate') }}" method="post">
        {!! csrf_field() !!}
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Create Ingredient</h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <div class="{{ $errors->has('name') ? 'has-error' : '' }}">
                <label class="col-sm-4 control-label">Ingredient Name</label>
              </div>
              <div class="col-sm-8 {{ $errors->has('name') ? 'has-error' : '' }}">
                <input type="text" name="name" class="form-control" placeholder="Ingredient Name" value="{{ old('name') }}">
                @if($errors->has('name'))
                  <span class="help-block">
                    <i>* {{$errors->first('name')}}</i>
                  </span>
                @endif
                <input type="hidden" name="user_id" value="{{ Auth::user()->id }}">
              </div>
            </div>
            <div class="form-group">
              <div class="{{ $errors->has('unit') ? 'has-error' : '' }}">
                <label class="col-sm-4 control-label">Ingredient Unit</label>
              </div>
              <div class="col-sm-8 {{ $errors->has('unit') ? 'has-error' : '' }}">
                <select name="unit" class="form-control">
                  <option value="-- Choose --">-- Choose --</option>
                  <option value="ea">ea</option>
                  <option value="gm">gm</option>
                  <option value="gm">ml</option>
                </select>
                @if($errors->has('unit'))
                  <span class="help-block">
                    <i>* {{$errors->first('unit')}}</i>
                  </span>
                @endif
                <input type="hidden" name="user_id" value="{{ Auth::user()->id }}">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="reset" class="btn btn-default pull-left btn-flat" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary bg-orange">Create Ingredients</button>
          </div>
        </div>
    </form>
    </div>
  </div>

  <div class="row">
    <div class="col-md-4">
      <a href="" data-toggle="modal" data-target="#myModalCreateIngredients" class="btn btn-flat bg-red">New Ingredients</a>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      @if(Session::has('message'))
        <br />
        <div class="alert alert-success">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
          <h4><i class="icon fa fa-check"></i> Succeed!</h4>
          <p>{{ Session::get('message') }}</p>
        </div>
      @endif
      @if(Session::has('success'))
        <br />
        <div class="alert alert-success">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
          <h4><i class="icon fa fa-check"></i> Succeed!</h4>
          <p>{{ Session::get('success') }}</p>
        </div>
      @endif
      <br />
    </div>
  </div>

  <div class="row">

    <div class="col-md-6">
      <div class="box box-danger">
        <div class="box-header">
          <h3 class="box-title">Master Ingredients</h3>
        </div>
        <div class="box-body">
          <table id="example1" class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>Name</th>
                <th>UOM</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($ingredients as $key)
              <tr>
                <td>{{ $key->name }}</td>
                <td>{{ $key->unit }}</td>
                <td><span data-toggle="tooltip" title="Edit Ingredient">
                  <a href="" class="btn btn-warning btn-flat btn-xs edit" data-toggle="modal" data-target="#myModalEditIngredient" data-value="{{ $key->id }}"><i class="fa fa-edit"> Edit</i></a>
                </span></td>
              </tr>
              @endforeach
            </tbody>
            <tfoot>
            <tr>
              <th>Name</th>
              <th>UOM</th>
              <th></th>
            </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

  </div>
@stop

@section('script')
<script src="{{ asset('plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js') }}"></script>
<script type="text/javascript">
@if (count($errors) > 0)
  $('#myModalCreateIngredients').modal('show');
@endif
</script>
<script>
  $(function () {
    $("#example1").DataTable();
  });
</script>
<script>

  $('a.edit').click(function(){
    var a = $(this).data('value');
    $.ajax({
      url: "{{ url('/') }}/hurricanesmenu/menu-ingredientsbind/"+a,
      dataType: 'json',
      success: function(data){
        //get
        var id = data.id;
        var name = data.name;
        var unit = data.unit;
        var user_id = data.user_id;

        // set
        $('#editId').attr('value', id);
        $('#editName').attr('value', name);
        $('#editUnit').attr('value', unit);
        $('#editUser').attr('value', user_id);
      }
    });
  });

  window.setTimeout(function() {
    $(".alert-success").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove();
    });
  }, 2000);
  window.setTimeout(function() {
    $(".alert-danger").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove();
    });
  }, 5000);
</script>

@endsection
