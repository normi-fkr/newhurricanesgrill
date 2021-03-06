<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

use App\Http\Requests;
use App\Models\Branch;
use App\Models\User;
use Auth;

use App\Http\Requests\BranchRequest;

class BranchController extends Controller
{
    /**
    * Authentication controller.
    *
    * @return void
    */
    public function __construct()
    {
        $this->middleware('isUser');
    }


    public function index()
    {
      $getBranch = Branch::join('fra_users', 'fra_users.id', '=', 'fra_branch.user_id')
                          ->select('fra_branch.*', 'fra_users.name as username')
                          ->get();

      return view('back.pages.branch.index', compact('getBranch'));
    }

    public function create()
    {

      return view('back.pages.branch.create');
    }

    public function store(BranchRequest $request)
    {
      $branch = new Branch;
      $branch->name   = $request->name;
      $branch->address  = $request->address;
      $branch->description  = $request->description;
      $branch->phone    = $request->phone;
      $branch->hotline  = $request->hotline;
      $branch->maps     = $request->maps;
      $branch->user_id  = $request->user_id;
      $branch->flag_active  = 0;
      $branch->save();

      return redirect()->route('branch')->with('message', 'New Branch Has Been Created and Not Yet Activated');
    }

    public function bind($id)
    {
      $get  = Branch::find($id);
      return $get;
    }

    public function update(BranchRequest $request)
    {
      $branch = Branch::find($request->id);
      $branch->name         = $request->name;
      $branch->address      = $request->address;
      $branch->description  = $request->description;
      $branch->phone        = $request->phone;
      $branch->hotline      = $request->hotline;
      $branch->maps         = $request->maps;
      $branch->user_id      = $request->user_id;
      $branch->flag_active  = $request->flag_active;
      $branch->save();

      return redirect()->route('branch')->with('message', 'Branch Data Has Been Updated');
    }

    public function nonactive($id)
    {
      $set = Branch::find($id);
      $set->flag_active = 0;
      $set->save();

      return redirect()->route('branch')->with('message', 'The Branch Has Been DeActivated');
    }

    public function active($id)
    {
      $set = Branch::find($id);
      $set->flag_active = 1;
      $set->save();

      return redirect()->route('branch')->with('message', 'The Branch Has Been Activated');
    }
}
