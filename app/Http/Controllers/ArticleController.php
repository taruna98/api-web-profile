<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    public function __construct()
    {
        //
    }
    
    public function index($id)
    {
        // get data from json file
        $folderPath = realpath(__DIR__ . '/../../../') . '\public\json\article';

        // get all file JSON in directory
        $jsonFiles = glob($folderPath . '/*.json');

        foreach ($jsonFiles as $jsonFile) {
            $jsonData = file_get_contents($jsonFile);
            $decodedData = json_decode($jsonData, true);
            if (strpos(substr($decodedData['id'], 0, 11), $id) !== false) {
                $dataload[] = $decodedData;
            }
        }

        return response()->json($dataload);
    }
    
    public function detail($id)
    {
        // get data from json file
        $folderPath = realpath(__DIR__ . '/../../../') . '\public\json\article';

        // get all file JSON in directory
        $jsonFiles = glob($folderPath . '/*.json');

        foreach ($jsonFiles as $jsonFile) {
            $jsonData = file_get_contents($jsonFile);
            $decodedData = json_decode($jsonData, true);
            if (strpos($decodedData['id'], $id) !== false) {
                $dataload[] = $decodedData;
            }
        }

        return response()->json($dataload);
    }

}
