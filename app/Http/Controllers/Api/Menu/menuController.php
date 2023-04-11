<?php

namespace App\Http\Controllers\Api\Menu;

use App\Http\Controllers\Controller;
use App\Models\assets;
use Illuminate\Http\Request;
use App\Models\menu;
use App\Models\menu1;
use App\Models\stock;
use App\Models\stock1;
use App\Models\menuBar;
use Illuminate\Support\Facades\DB;

class menuController extends Controller
{
    protected $out;
    public function __construct()
    {
        $this->out = new \Symfony\Component\Console\Output\ConsoleOutput();
    }

    private function getIDfromReq()
    {
        $id = app('request')->__get('id');
        abort_unless(
            ($id !== null || !is_int($id)),
            403,
            json_encode([
                'state' => 'user_id',
                'message' => 'This user ID is poorly formated'
            ]),
        );
        return $id;
    }

    public function getMenuKitchen(Request $request)
    {
        $menuMajor = menu::all();
        $menuMinor = menu1::all();
        $menuKitchen = (object) array('Major' => $menuMajor, 'Minor' => $menuMinor);
        return response([
            'result' => true,
            'menuMajor' => $menuMajor,
            'menuMinor' => $menuMinor,
            'menuKitchen' => $menuKitchen
        ], 200);
    }

    public function getMenuBar(Request $request)
    {
        $menuBar = menuBar::all();
        $stockBar = assets::where('section', 'SERVICE-BAR')->get();
        return response([
            'result' => true,
            'menuBar' => $menuBar,
            'stockBar' => $stockBar
        ], 200);
    }

    public function delMenu(Request $request)
    {
        $this->validate($request, [
            'type' => 'required_with:id|string|min:3|max:255',
            'id' => 'required_with:type|integer|min:1|digits_between: 1,5000'
        ]);

        $type = app('request')->__get('type');
        $id = $this->getIDfromReq();
        DB::beginTransaction();
        try {
            if ($type == 'Major') {
                $smenu = menu::where('menus_id', $id)->first();
                $category = $smenu->category;
                $op =  menu::where('menus_id', $id)->delete();
            } elseif ($type == 'Minor') {
                $smenu = menu1::where('menu1s_id', $id)->first();
                $category = $smenu->category;
                $op =  menu1::where('menu1s_id', $id)->delete();
            }
            DB::commit();
            return response([
                'result' => $op,
                'category' => $category
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function addMenu(Request $request)
    {
        $this->validate($request, [
            'type' => 'required|string|min:3|max:255'
        ]);

        $type = app('request')->__get('type');

        DB::beginTransaction();
        try {
            if ($type == 'Major') {
                $this->validate($request, [
                    'category' => 'required|string|min:3|max:255'
                ]);

                $menu = new menu;
                $menu->category = app('request')->__get('category');
                $menu->save();
            } elseif ($type == 'Minor') {
                $this->validate($request, [
                    'id' => 'required_with:category|integer|min:1|digits_between: 1,5000',
                    'category' => 'required_with:id|string|min:3|max:255',
                    'price' => 'required|integer|min:100|max:5000000'
                ]);

                $menu = new menu1;
                $menu->menus_id = app('request')->__get('id');
                $menu->category = app('request')->__get('category');
                $menu->description = app('request')->__get('description');
                $menu->price = app('request')->__get('price');
                $menu->save();
            }
            DB::commit();
            return response([
                'result' => true,
                'category' => app('request')->__get('category')
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function addMenuBar(Request $request)
    {
        $this->validate($request, [
            'stock1s_id' => 'required_with:category|integer|min:1|digits_between: 1,5000',
            'category' => 'required_with:stocks|string|min:3|max:255',
            'stocks' => 'required_with:description|string|min:3|max:255',
            'description' => 'required_with:price|string|min:3|max:255',
            'price' => 'required|integer|min:1|digits_between: 1,5000000'
        ]);

        DB::beginTransaction();
        try {
            //$menu_ = menuBar::where('menu_bars_id', $id)->first();



            $menu = new menuBar;
            $menu->stock1s_id = app('request')->__get('stock1s_id');
            $menu->category = app('request')->__get('category');
            $menu->stocks = app('request')->__get('stocks');
            $menu->description = app('request')->__get('description');
            $menu->price = app('request')->__get('price');
            $menu->save();
            DB::commit();
            return response([
                'result' => true,
                'category' => app('request')->__get('category')
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function delMenuBar(Request $request)
    {
        
        $this->validate($request, [
            'id' => 'required_with:type|integer|min:1|digits_between: 1,5000'
        ]);

        $id = $this->getIDfromReq();
        DB::beginTransaction();
        try {
            $menu = menuBar::where('menu_bars_id', $id)->first();
            $category = $menu->category;
            $op =  menuBar::where('menu_bars_id', $id)->delete();

            DB::commit();
            return response([
                'result' => $op,
                'category' => $category
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function getAllMenu (Request $request) 
    {
        $menu = menu::all();
        $menuKitchen = array();
        foreach ($menu as $menunode) {
            $menus_id  = $menunode->menus_id;
            $category  = $menunode->category;
            $menus1 = menu1::where('menus_id', $menus_id)->get();
            foreach ($menus1 as $menus1node) {
                $menu1Object = new menu1Object();
                $menu1Object->set_section('KITCHEN');
                $menu1Object->set_menu_id($menus1node->menu1s_id);
                $menu1Object->set_stock1s_id($menus1node->menus_id);
                $menu1Object->set_category($category);
                $menu1Object->set_stocks($menus1node->category);
                $menu1Object->set_description($menus1node->description);
                $menu1Object->set_price($menus1node->price);
                $menu1Object->set_created_at($menus1node->created_at);
                $menu1Object->set_updated_at($menus1node->updated_at);
                array_push($menuKitchen, $menu1Object);
            }
        }

        $menuBar = menuBar::all();
        $collectionWithKey = $menuBar->map(function ($item) {
            $item['section'] = "SERVICE-BAR";
            return $item;
        });

        $menu = (object) array('Kitchen' => $menuKitchen, 'Bar' => $collectionWithKey);
        return response([
            'result' => true,
            'menu' => $menu,
        ], 200);
    }
}



class stockObject
{
    // Properties
    public $stocks_id;
    public $category;
    public $created_at;
    public $updated_at;
    public $stock;

    // Methods
    function set_stocks_id($stocks_id)
    {
        $this->stocks_id = $stocks_id;
    }
    function get_stocks_id()
    {
        return $this->stocks_id;
    }

    function set_category($category)
    {
        $this->category = $category;
    }
    function get_category()
    {
        return $this->category;
    }

    function set_created_at($created_at)
    {
        $this->created_at = $created_at;
    }
    function get_created_at()
    {
        return $this->created_at;
    }

    function set_updated_at($updated_at)
    {
        $this->updated_at = $updated_at;
    }
    function get_updated_at()
    {
        return $this->updated_at;
    }

    function set_stock($stock)
    {
        $this->stock = $stock;
    }
    function get_stock()
    {
        return $this->stock;
    }
}


class menu1Object
{
    // Properties
    public $section;
    public $menu_kitchen_id;
    public $stock1s_id;
    public $category;
    public $stocks;
    public $description;
    public $price;
    public $created_at;
    public $updated_at;

    // Methods
    function set_section($section)
    {
        $this->section = $section;
    }
    function get_section()
    {
        return $this->section;
    }

    function set_menu_id($menu_id)
    {
        $this->menu_kitchen_id = $menu_id;
    }
    function get_menu_id()
    {
        return $this->menu_kitchen_id;
    }

    function set_stock1s_id($stock1s_id)
    {
        $this->stock1s_id = $stock1s_id;
    }
    function get_stock1s_id()
    {
        return $this->stock1s_id;
    }

    function set_category($category)
    {
        $this->category = $category;
    }
    function get_category()
    {
        return $this->category;
    }

    function set_stocks($stocks)
    {
        $this->stocks = $stocks;
    }
    function get_stocks()
    {
        return $this->stocks;
    }

    function set_description($description)
    {
        $this->description = $description;
    }
    function get_description()
    {
        return $this->description;
    }

    function set_price($price)
    {
        $this->price = $price;
    }
    function get_price()
    {
        return $this->price;
    }

    function set_created_at($created_at)
    {
        $this->created_at = $created_at;
    }
    function get_created_at()
    {
        return $this->created_at;
    }

    function set_updated_at($updated_at)
    {
        $this->updated_at = $updated_at;
    }
    function get_updated_at()
    {
        return $this->updated_at;
    }
}