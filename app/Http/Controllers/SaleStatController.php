<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SaleStatController extends Controller
{

    static $actionPermissionMap = [
        'list' => 'SaleStatController:list',
        'findById' => 'SaleStatController:findById',
        'create' => 'SaleStatController:create',
        'update' => 'SaleStatController:update',
        'delete' => 'SaleStatController:delete'
    ];

    public function __construct()
    {
        //
    }

    public function list()
    {
        //
    }


    public function findById($id)
    {
        //
    }


    public function create(Request $request)
    {
        //
    }


    public function update(Request $request, $id)
    {
        //
    }


    public function delete($id)
    {
        //
    }
}
