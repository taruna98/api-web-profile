<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function __construct()
    {
        //
    }
    
    public function index($id)
    {
        $profile = DB::table('profiles')
            ->select('id', 'cod', 'nme', 'hsb', 'mds', 'msk', 'ssb', 'sci', 'sct', 'scd', 'created_at', 'updated_at')
            ->where('cod', $id)
            ->first();

        // get data portfolio from json file
        $folderPath = realpath(__DIR__ . '/../../../') . '\public\json\portfolio';

        // get all file JSON in directory
        $jsonFiles = glob($folderPath . '/*.json');

        foreach ($jsonFiles as $jsonFile) {
            $jsonData = file_get_contents($jsonFile);
            $decodedData = json_decode($jsonData, true);
            if (strpos($decodedData['id'], $profile->cod) !== false) {
                $data['id']     = $decodedData['id'];
                $data['ttl']    = $decodedData['ttl'];
                $data['hbg']    = $decodedData['hbg'];
                $data['ctg']    = $decodedData['ctg'];
                $data['cat']    = $decodedData['cat'];
                $portfolio[]    = $data;
            }
        }

        $dataload = [
            'profile'   => $profile,
            'portfolio' => $portfolio
        ];
        
        return response($dataload, 200);
    }

}
