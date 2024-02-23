<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;


class ProfileController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function index($id)
    {
        // get profile
        $profile = DB::table('profiles')
            ->where('cod', $id)
            ->first();
        if ($profile == null) {
            return response([], 200);
        }

        // get user
        $user = DB::connection('mysql2')->table('users')->where('email', $profile->eml)->first();
        if ($user == null) {
            return response([], 200);
        }

        // join data profile
        $profile->nme = $user->name;
        $profile->stt = $user->is_active;

        // get data profile from json file
        $folderPathPro = realpath(__DIR__ . '/../../../') . '\public\json\profile';

        // get all file JSON in directory profile
        $jsonFilesPro = glob($folderPathPro . '/*.json');

        foreach ($jsonFilesPro as $jsonFilePro) {
            $jsonDataPro = file_get_contents($jsonFilePro);
            $decodedDataPro = json_decode($jsonDataPro, true);
            if (strpos($decodedDataPro['profile']['cod'], $profile->cod) !== false) {
                $decodedDataPro['profile']['nme']  = $profile->nme;
                $decodedDataPro['profile']['stt']  = $profile->stt;
                $profile    = $decodedDataPro;
            } else {
                return response([], 200);
            }
        }

        $dataload = $profile;
        return response($dataload, 200);
    }

    public function portfolio_detail($id)
    {
        // get data from json file
        $folderPath = realpath(__DIR__ . '/../../../') . '\public\json\profile';

        // get all file JSON in directory
        $jsonFiles = glob($folderPath . '/*.json');

        foreach ($jsonFiles as $jsonFile) {
            $jsonData = file_get_contents($jsonFile);
            $decodedData = json_decode($jsonData, true);
            $email = $decodedData['profile']['eml'];
            $name = $decodedData['profile']['nme'];
            foreach ($decodedData['portfolio'] as $dataload) {
                if ($dataload['id'] == $id) {
                    $dataload['nme'] = $name;
                    $dataload['eml'] = $email;
                    return response()->json($dataload);
                }
            }
            return response([], 200);
        }
    }

    public function article_detail($id)
    {
        // get data from json file
        $folderPath = realpath(__DIR__ . '/../../../') . '\public\json\profile';

        // get all file JSON in directory
        $jsonFiles = glob($folderPath . '/*.json');

        foreach ($jsonFiles as $jsonFile) {
            $jsonData = file_get_contents($jsonFile);
            $decodedData = json_decode($jsonData, true);
            $email = $decodedData['profile']['eml'];
            $name = $decodedData['profile']['nme'];
            foreach ($decodedData['article'] as $dataload) {
                if ($dataload['id'] == $id) {
                    $dataload['nme'] = $name;
                    $dataload['eml'] = $email;
                    return response()->json($dataload);
                }
            }
            return response([], 200);
        }
    }
}
