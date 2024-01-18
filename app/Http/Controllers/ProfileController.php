<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($id)
    {
        $profile = DB::table('profiles')
            ->select('id', 'cod', 'nme', 'hsb', 'mds', 'msk', 'mtl', 'ssb', 'sci', 'sct', 'scd', 'created_at', 'updated_at')
            ->where('cod', $id)
            ->first();
            
        if ($profile == null) {
            return response([], 200);
        }

        // get data portfolio from json file
        $folderPathPor = realpath(__DIR__ . '/../../../') . '\public\json\portfolio';

        // get all file JSON in directory portfolio
        $jsonFilesPor = glob($folderPathPor . '/*.json');

        foreach ($jsonFilesPor as $jsonFilePor) {
            $jsonDataPor = file_get_contents($jsonFilePor);
            $decodedDataPor = json_decode($jsonDataPor, true);
            if (strpos($decodedDataPor['id'], $profile->cod) !== false) {
                $dataPor['id']  = $decodedDataPor['id'];
                $dataPor['ttl'] = $decodedDataPor['ttl'];
                $dataPor['ctg'] = $decodedDataPor['ctg'];
                $dataPor['cat'] = $decodedDataPor['cat'];
                $portfolio[]    = $dataPor;
            }
        }

        // get data article from json file
        $folderPathArt = realpath(__DIR__ . '/../../../') . '\public\json\article';

        // get all file JSON in directory article
        $jsonFilesArt = glob($folderPathArt . '/*.json');

        foreach ($jsonFilesArt as $jsonFileArt) {
            $jsonDataArt = file_get_contents($jsonFileArt);
            $decodedDataArt = json_decode($jsonDataArt, true);
            if (strpos($decodedDataArt['id'], $profile->cod) !== false) {
                $dataArt['id']  = $decodedDataArt['id'];
                $dataArt['nme'] = $decodedDataArt['nme'];
                $dataArt['ttl'] = $decodedDataArt['ttl'];
                $dataArt['ctg'] = $decodedDataArt['ctg'];
                $dataArt['cat'] = $decodedDataArt['cat'];
                $article[]      = $dataArt;
            }
        }

        $dataload = [
            'profile'   => $profile,
            'portfolio' => $portfolio,
            'article'   => $article
        ];

        return response($dataload, 200);
    }
}
