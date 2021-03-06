<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\MenuCategory;
use App\Models\Menus;
use App\Models\Ingredients;
use App\Models\RecipeMenu;
use Auth;
use DB;
use Validator;
use Image;


class MenuController extends Controller
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


    public function category()
    {
      $categoryMenus = MenuCategory::where('flag_active', 1)->get();
      $menus  = Menus::get();

      return view('back.pages.menu.category', compact('categoryMenus', 'menus'));
    }


    public function categoryCreate(Request $request)
    {
      $message = [
        'name.required' => 'Fill This Field',
        'name.unique' => 'This Category Already Exsist'
      ];

      $validator = Validator::make($request->all(), [
        'name'  => 'required|unique:fra_menucategory',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('menu.category')->withErrors($validator)->withInput();
      }

      $categoryMenu = new MenuCategory;
      $categoryMenu->name = $request->name;
      $categoryMenu->user_id  = $request->user_id;
      $categoryMenu->flag_active = 1;
      $categoryMenu->save();

      return redirect()->route('menu.category')->with('success','New Category Menu Has Been Created');
    }

    public function categoryBind($id)
    {
      $bindCategory = MenuCategory::find($id);

      return $bindCategory;
    }

    public function categoryUpdate(Request $request)
    {
      $categoryUpdate = MenuCategory::find($request->editId);
      $categoryUpdate->name = $request->editName;
      $categoryUpdate->user_id  = $request->editUser_id;
      $categoryUpdate->flag_active  = 1;
      $categoryUpdate->save();

      return redirect()->route('menu.category')->with('success','Category Menu Has Been Updated');
    }

    public function categoryTrash($id)
    {
      $user = Auth::user()->id;

      $trash = MenuCategory::find($id);
      $trash->flag_active = 0;
      $trash->user_id = $user;
      $trash->save();

      return redirect()->route('menu.category')->with('success','Category Successfully Removed');
    }


    public function ingredients()
    {

      $ingredients = Ingredients::get();

      return view('back.pages.menu.ingredients', compact('ingredients'));
    }

    public function ingredientsCreate(Request $request)
    {
      $message  = [
        'name.required' => 'Fill This Field',
        'name.unique'   => 'The Ingredient Has Already Been Taken',
        'unit.required' => 'Fill This Field'
      ];

      $validator = Validator::make($request->all(), [
        'name'  =>  'required|unique:fra_ingredients',
        'unit'  =>  'required',
      ], $message);

      if($validator->fails()){
        return redirect()->route('menu.ingredients')->withErrors($validator)->withInput();
      }

      $createIngredients = new Ingredients;
      $createIngredients->name  = $request->ingredientName;
      $createIngredients->unit  = $request->ingredientUnit;
      $createIngredients->user_id = $request->user_id;
      $createIngredients->save();

      return redirect()->route('menu.ingredients')->with('success', 'New Ingredients Successfully Created');
    }

    public function ingredientsBind($id)
    {
      $ingredients = Ingredients::find($id);

      return $ingredients;
    }

    public function ingredientsUpdate(Request $request)
    {
      $update = Ingredients::find($request->editId);
      $update->name = $request->editName;
      $update->unit = $request->editUnit;
      $update->user_id  = $request->editUser_id;
      $update->save();

      return redirect()->route('menu.ingredients')->with('success', 'Ingredients Has Been Updated');
    }

    public function menus()
    {
      $menus  = Menus::join('fra_menucategory', 'fra_menucategory.id', '=', 'fra_menus.menucategory_id')
                      ->select('fra_menus.*', 'fra_menucategory.name as categoryName')
                      ->where('fra_menus.flag_active', 1)
                      ->get();

      $categoryMenus       = MenuCategory::where('flag_active', 1)->get();

      return view('back.pages.menu.menus', compact('menus', 'categoryMenus'));
    }

    public function menusCreate(Request $request)
    {
      $message  = [
        'name.required' => 'Fill This Field',
        'name.unique' => 'Ingredient Has Already Been Taken',
        'menucategory_id.required' => 'Fill This Field'
      ];

      $validator  = Validator::make($request->all(), [
        'name'  => 'required|unique:fra_menus',
        'menucategory_id' => 'required|not_in:-- Choose --'
      ], $message);

      if($validator->fails()){
        return redirect()->route('menu.menus')->withErrors($validator)->withInput();
      }

      $menusCreate = new Menus;
      $menusCreate->name  = $request->name;
      $menusCreate->menucategory_id = $request->menucategory_id;
      $menusCreate->user_id  = $request->user_id;
      $menusCreate->flag_active = 1;
      $menusCreate->save();

      return redirect()->route('menu.menus')->with('message', 'New Menu Has Been Created, Please Input the Recipe Then');

    }

    public function menusShow($id)
    {

      $menus  = Menus::join('fra_menucategory', 'fra_menucategory.id', '=', 'fra_menus.menucategory_id')
                      ->select('fra_menus.*', 'fra_menucategory.name as category')
                      ->where('fra_menus.id', $id)
                      ->get();

      $ingredients = RecipeMenu::join('fra_ingredients', 'fra_ingredients.id', '=', 'fra_recipemenu.ingredients_id')
                                ->select('fra_recipemenu.size', 'fra_ingredients.name', 'fra_ingredients.unit', 'fra_recipemenu.notes')
                                ->where('fra_recipemenu.menu_id', $menus[0]->id)
                                ->get();

      return view('back.pages.menu.menusshow', compact('menus', 'ingredients'));
    }

    public function menuImage(Request $request)
    {
      $message  = [
        'image.required' => 'Fill This Field',
        'image.dimensions'  => 'Max Width: 445px, Max Height: 350px',
      ];

      $validator  = Validator::make($request->all(), [
        'image'  => 'required|mimes:jpeg,jpg,png|dimensions:max_width=445,max_height=350',
      ], $message);

      if($validator->fails()){
        return redirect()->route('menu.menusShow', array('id' => $request->menu_id))->withErrors($validator)->withInput();
      }

      $set = Menus::find($request->menu_id);

      $file = $request->file('image');

      $photo_name = $set->name. '.' . $file->getClientOriginalExtension();
      Image::make($file)->resize(443,350)->save('images/'. $photo_name);

      $set->image = $photo_name;
      $set->user_id = $request->user_id;
      $set->save();

      return redirect()->route('menu.menusShow', array('id' => $request->menu_id))->with('message', 'Profile Has Been Changed.');
    }

    public function recipeCreate($id)
    {
      $menus   = Menus::find($id);
      // dd($menus->id);
      $ingredients  = Ingredients::get();

      return view('back.pages.menu.recipecreate', compact('menus', 'ingredients'));
    }

    public function recipeStore(Request $request)
    {
      $message  = [
        'ingredients.*.size.required' => 'Fill This Field',
        'ingredients.*.ingredient.required'  => 'Fill This Field',
      ];

      $validator = Validator::make($request->all(), [
        'ingredients.*.size'  => 'required',
        'ingredients.*.ingredient' => 'required',
      ], $message);

      if($validator->fails()){
        return redirect()->route('menu.recipeCreate', array('id' => $request->menu_id))->withErrors($validator)->withInput();
      }

      DB::transaction(function() use($request) {
        $recipes = $request->input('ingredients');
        if($recipes != ""){
          foreach($recipes as $recipe){
            $create = new RecipeMenu;
            $create->ingredients_id = $recipe['ingredient'];
            $create->size           = $recipe['size'];
            $create->menu_id        = $request->menu_id;
            $create->notes          = $recipe['notes'];
            $create->user_id        = $request->user_id;
            $create->save();
          }
        }

      });

      return redirect()->route('menu.menusShow', array('id' => $request->menu_id))->with('message','New Recipe Has Been Created.');
    }

    public function recipeEdit($id)
    {
      $menus   = Menus::find($id);

      $recipes  = RecipeMenu::join('fra_ingredients', 'fra_ingredients.id', '=', 'fra_recipemenu.ingredients_id')
                            ->select('fra_ingredients.id as ingredients_id', 'fra_ingredients.*', 'fra_recipemenu.*')
                            ->where('fra_recipemenu.menu_id', $id)->get();

      $ingredients  = Ingredients::get();

      return view('back.pages.menu.recipeedit', compact('menus', 'ingredients', 'recipes'));
    }

    public function recipeAdd(Request $request)
    {
      $menu_id = $request->menu_id;

      $message  = [
        'size.required' => 'Fill This Field',
        'ingredients.required'  => 'Fill This Field',
      ];

      $validator = Validator::make($request->all(), [
        'size'  => 'required',
        'ingredients' => 'required',
      ], $message);

      if($validator->fails()){
        return redirect()->route('menu.recipeEdit', array('id' => $menu_id))->withErrors($validator)->withInput();
      }

      $set = new RecipeMenu;
      $set->ingredients_id  = $request->ingredients;
      $set->size            = $request->size;
      $set->menu_id         = $request->menu_id;
      $set->notes           = $request->notes;
      $set->user_id         = $request->user_id;
      $set->save();

      return redirect()->route('menu.recipeEdit', array('id' => $menu_id))->with('message', 'New Ingredient Has Been Added');
    }

    public function recipeBind($id)
    {
      $bind = RecipeMenu::find($id);

      return $bind;
    }

    public function recipeChange(Request $request)
    {
      $menu_id  = $request->menu_id;

      $message  = [
        'editSize.required' => 'Fill This Field',
        'editIngredients_id.required'  => 'Fill This Field',
      ];

      $validator = Validator::make($request->all(), [
        'editSize'  => 'required',
        'editIngredients_id' => 'required',
      ], $message);

      if($validator->fails()){
        return redirect()->route('menu.recipeEdit', array('id' => $menu_id))->withErrors($validator)->withInput();
      }

      $update = RecipeMenu::find($request->editId);
      $update->ingredients_id  = $request->editIngredients_id;
      $update->size = $request->editSize;
      $update->notes  = $request->editNotes;
      $update->user_id  = $request->user_id;
      $update->save();

      return redirect()->route('menu.recipeEdit', array('id' => $menu_id))->with('message', 'The Ingredient Successfully Changed');
    }

    public function recipeDelete($id)
    {
      $delete = RecipeMenu::find($id);
      $delete->delete();
    }

    public function directionsCreate($id)
    {
      // dd($id);
      $menus   = Menus::find($id);
      // dd($menus->id);
      $ingredients  = Ingredients::get();

      return view('back.pages.menu.directionscreate', compact('menus', 'ingredients'));
    }

    public function directionsStore(Request $request)
    {
      $menu_id  = $request->menu_id;

      $message  = [
        'directions.required' => 'Fill This Field',
      ];

      $validator = Validator::make($request->all(), [
        'directions'  => 'required',
      ], $message);

      if($validator->fails()){
        return redirect()->route('menu.directionsAdd', array('id' => $menu_id))->withErrors($validator)->withInput();
      }

      $directions = Menus::find($menu_id);
      $directions->directions = $request->directions;
      $directions->user_id    = $request->user_id;
      $directions->save();

      return redirect()->route('menu.menusShow', array('id' => $menu_id))->with('message', 'New Directions Has Been Created');
    }

    public function directionsEdit($id)
    {
      $menus  = Menus::select('id', 'name', 'directions')->find($id);

      return view('back.pages.menu.directionsedit', compact('menus'));
    }

}
