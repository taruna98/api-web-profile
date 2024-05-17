<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        $folderPathPro = realpath(__DIR__ . '/../../../') . '/public/json/profile/';

        // get file json
        $jsonFilePro = glob($folderPathPro . $profile->cod . '.json');

        if ($jsonFilePro === false) {
            return response([], 404);
        }

        $jsonDataPro = file_get_contents($jsonFilePro[0]);
        $decodedDataPro = json_decode($jsonDataPro, true);

        if ($decodedDataPro === null) {
            return response([], 403);
        }

        // check upload cv
        $check_profile_cv = DB::connection('mysql2')->table('log_activity')->select('activity')->where('user_id', $user->id)->where('scene', 'Content/Profile/CV')->where('activity', 'like', '% Profile CV')->orderBy('created_at', 'desc')->first();

        if ($check_profile_cv != '') {
            $check_profile_cv = explode('-', $check_profile_cv->activity)[2];
            $check_profile_cv = explode(' ', $check_profile_cv)[1];
            $status_profile_cv = ($check_profile_cv && $check_profile_cv == 'Delete') ? '-1' : ($check_profile_cv && $check_profile_cv == 'Upload' ? '1' : '0');
        } else {
            $status_profile_cv = '0';
        }

        $decodedDataPro['profile']['nme']  = $profile->nme;
        $decodedDataPro['profile']['stt']  = $profile->stt;
        $decodedDataPro['profile']['scv']  = $status_profile_cv;
        $profile = $decodedDataPro;

        $dataload = $profile;
        return response($dataload, 200);
    }

    public function request(Request $request)
    {
        // this function for validation email and get status 1 for send email and waiting approval, 2 for process generate, -2 not generate, 3 success registration, -3 failed registration

        $validator = Validator::make($request->all(), [
            'email'     => 'required|email',
            'status'    => 'required'
        ]);

        if ($validator->fails()) {
            return response('not acceptable', 406);
        }

        // email validation

        // check email exists in system (table users), if exists return status already exists

        // descission by status
        // if status 1, send message to that email (click link for verification), save to table task opsadmin waiting for accept by admin and set status 2
        // if status 2, create profile user in table users opsadmin, create user in api table profiles include generate code, hit api for store (create json file in api project), send message to that email (success register), set status 3 (success)
        // if status -2, set status -3 (denied), send message to that email (denied register) 


        return $request;
    }

    public function store($id)
    {
        // get profile
        $profile = DB::table('profiles')
            ->where('cod', $id)
            ->first();
        if ($profile == null) {
            return response('not found', 404);
        }

        $folderPathPro = realpath(__DIR__ . '/../../../') . '\public\json\profile';
        $file_name = $id . '.json';
        $file_path = $folderPathPro . DIRECTORY_SEPARATOR . $file_name;

        // check file json from folder profile
        if (file_exists($file_path) && is_file($file_path)) {
            return response('file already exist', 403);
        }

        // defined variable
        $file_id    = $profile->id;
        $code       = $id;
        $email      = $profile->eml;
        $name       = $profile->nme;
        $time_now   = date('Y-m-d H:i:s');

        // data for file json
        $data = '{
            "profile": {
                "id": ' . $file_id . ',
                "cod": "' . $code . '",
                "eml": "' . $email . '",
                "nme": "' . $name . '",
                "hsb": "dummy header",
                "mds": "Hello, let\'s fill your description",
                "msk": "FIRST SKILL|SECOND SKILL|THIRD SKILL",
                "mtl": "First Tool|Second Tool|Third Tool",
                "ssb": "This is my service",
                "sci": "fas fa-cube|fab fa-cube|fas fa-cube",
                "sct": "first profession|second profession|third profession",
                "scd": "Your first profession description.|Your second profession description.|Your third profession description.",
                "stt": "1",
                "created_at": "' . $time_now . '",
                "updated_at": "' . $time_now . '"
            },
            "portfolio": [],
            "article": []
        }';

        $create_json = file_put_contents($file_path, $data);

        if (!$create_json) {
            return response('failed create file', 403);
        }

        return response('success create file', 200);
    }

    public function update(Request $request, $id)
    {
        // get data profile from json file
        $folderPathPro = realpath(__DIR__ . '/../../../') . '/public/json/profile/';

        // get file json
        $jsonFilePro = glob($folderPathPro . $id . '.json');

        if ($jsonFilePro === false) {
            return response([], 404);
        }

        $jsonDataPro = file_get_contents($jsonFilePro[0]);
        $decodedDataPro = json_decode($jsonDataPro, true);

        if ($decodedDataPro === null) {
            return response([], 403);
        }

        // get params
        $name       = $request->name;
        $about      = $request->about;
        $profession = $request->profession;
        $tools      = $request->tools;
        $skill      = $request->skill;

        // setup update json profile fields
        $decodedDataPro['profile']['nme'] = $name;
        $decodedDataPro['profile']['mds'] = $about;
        $decodedDataPro['profile']['hsb'] = $profession;
        $decodedDataPro['profile']['mtl'] = $tools;
        $decodedDataPro['profile']['msk'] = $skill;
        $profile = json_encode($decodedDataPro);

        // update profile field file json
        $filePathPro = $folderPathPro . $id . '.json';
        $update_json = file_put_contents($filePathPro, $profile);

        if (!$update_json) {
            return response('failed update file', 403);
        }

        return response('success update file', 200);
    }

    public function portfolio_detail($id)
    {
        // validation parameter
        if ((strlen($id) != 15) || str_contains($id, '-') != 1) {
            return response('forbidden', 403);
        }

        // get parameter
        $code       = explode('-', $id)[0];
        $id         = explode('-', $id)[1];
        $get_file   = $code . '.json';

        // get data from json file
        $folderPath = realpath(__DIR__ . '/../../../') . '\public\json\profile';

        // get all file JSON in directory
        $jsonFiles = glob($folderPath . '/*.json');

        foreach ($jsonFiles as $jsonFile) {
            $jsonData = file_get_contents($jsonFile);
            $decodedData = json_decode($jsonData, true);
            $fileName = basename($jsonFile);

            if ($fileName === $get_file) {
                $email = $decodedData['profile']['eml'];
                $name = $decodedData['profile']['nme'];
                $code = $decodedData['profile']['cod'];
                foreach ($decodedData['portfolio'] as $dataload) {
                    if ($dataload['id'] == $id) {
                        $dataload['eml'] = $email;
                        $dataload['nme'] = $name;
                        $dataload['cod'] = $code;
                        return response()->json($dataload);
                    }
                }
                return response([], 200);
            }
        }
        return response('not found', 404);
    }

    public function portfolio_store(Request $request, $id)
    {
        // get data profile from json file
        $folderPathPro = realpath(__DIR__ . '/../../../') . '/public/json/profile/';

        // get file json
        $jsonFilePro = glob($folderPathPro . $id . '.json');

        if ($jsonFilePro === false) {
            return response([], 404);
        }

        $jsonDataPro = file_get_contents($jsonFilePro[0]);
        $decodedDataPro = json_decode($jsonDataPro, true);

        if ($decodedDataPro === null) {
            return response([], 403);
        }

        // check id portfolio
        if (in_array($request->id, array_column($decodedDataPro['portfolio'], 'id'))) {
            return response('failed store portfolio', 403);
        }

        // get params
        $port_id        = $request->id;
        $title          = $request->title;
        $category       = $request->category;
        $client         = $request->client;
        $link           = $request->link;
        $content_title  = $request->content_title;
        $content_desc   = $request->content_desc;
        $status         = $request->status;
        $created_at     = Date('Y-m-d H:i:s');
        $updated_at     = Date('Y-m-d H:i:s');

        // setup row data store json portfolio fields
        $portfolio_data = [
            'id'    => $port_id,
            'ttl'   => $title,
            'ctg'   => $category,
            'cln'   => $client,
            'lnk'   => $link,
            'sbt'   => $content_title,
            'dsc'   => $content_desc,
            'stt'   => $status,
            'cat'   => $created_at,
            'uat'   => $updated_at
        ];

        // add row data into field portfolio
        $decodedDataPro['portfolio'][] = $portfolio_data;
        $portfolio = json_encode($decodedDataPro);

        // store profile field file json
        $filePathPro = $folderPathPro . $id . '.json';
        $update_json = file_put_contents($filePathPro, $portfolio);

        if (!$update_json) {
            return response('failed store portfolio', 403);
        }

        return response('success store portfolio', 200);
    }

    public function portfolio_update(Request $request, $id)
    {
        // get data profile from json file
        $folderPathPro = realpath(__DIR__ . '/../../../') . '/public/json/profile/';

        // get file json
        $jsonFilePro = glob($folderPathPro . $id . '.json');

        if ($jsonFilePro === false) {
            return response([], 404);
        }

        $jsonDataPro = file_get_contents($jsonFilePro[0]);
        $decodedDataPro = json_decode($jsonDataPro, true);

        if ($decodedDataPro === null) {
            return response([], 403);
        }

        // check id portfolio
        if (!in_array($request->id, array_column($decodedDataPro['portfolio'], 'id'))) {
            return response('failed update portfolio', 403);
        }

        // get params
        $port_id        = $request->id;
        $title          = $request->title;
        $category       = $request->category;
        $client         = $request->client;
        $link           = $request->link;
        $content_title  = $request->content_title;
        $content_desc   = $request->content_desc;
        $status         = $request->status;
        $created_at     = $request->created_at;
        $updated_at     = Date('Y-m-d H:i:s');

        // setup row data store json portfolio fields
        $portfolio_data = [
            'id'    => $port_id,
            'ttl'   => $title,
            'ctg'   => $category,
            'cln'   => $client,
            'lnk'   => $link,
            'sbt'   => $content_title,
            'dsc'   => $content_desc,
            'stt'   => $status,
            'cat'   => $created_at,
            'uat'   => $updated_at
        ];

        // update row data in portfolio by id
        foreach ($decodedDataPro['portfolio'] as &$item) {
            if ($item['id'] === $portfolio_data['id']) {
                $item = $portfolio_data;
                break;
            }
        }
        $portfolio = json_encode($decodedDataPro);

        // update profile field file json
        $filePathPro = $folderPathPro . $id . '.json';
        $update_json = file_put_contents($filePathPro, $portfolio);

        if (!$update_json) {
            return response('failed update portfolio', 403);
        }

        return response('success update portfolio', 200);
    }

    public function article_detail($id)
    {
        // validation parameter
        if ((strlen($id) != 15) || str_contains($id, '-') != 1) {
            return response('forbidden', 403);
        }

        // get parameter
        $code       = explode('-', $id)[0];
        $id         = explode('-', $id)[1];
        $get_file   = $code . '.json';

        // get data from json file
        $folderPath = realpath(__DIR__ . '/../../../') . '\public\json\profile';

        // get all file JSON in directory
        $jsonFiles = glob($folderPath . '/*.json');

        foreach ($jsonFiles as $jsonFile) {
            $jsonData = file_get_contents($jsonFile);
            $decodedData = json_decode($jsonData, true);
            $fileName = basename($jsonFile);

            if ($fileName === $get_file) {
                $email = $decodedData['profile']['eml'];
                $name = $decodedData['profile']['nme'];
                $code = $decodedData['profile']['cod'];
                foreach ($decodedData['article'] as $dataload) {
                    if ($dataload['id'] == $id) {
                        $dataload['eml'] = $email;
                        $dataload['nme'] = $name;
                        $dataload['cod'] = $code;
                        return response()->json($dataload);
                    }
                }
                return response([], 200);
            }
        }
        return response('not found', 404);
    }

    public function article_store(Request $request, $id)
    {
        // get data profile from json file
        $folderPathPro = realpath(__DIR__ . '/../../../') . '/public/json/profile/';

        // get file json
        $jsonFilePro = glob($folderPathPro . $id . '.json');

        if ($jsonFilePro === false) {
            return response([], 404);
        }

        $jsonDataPro = file_get_contents($jsonFilePro[0]);
        $decodedDataPro = json_decode($jsonDataPro, true);

        if ($decodedDataPro === null) {
            return response([], 403);
        }

        // check id article
        if (in_array($request->id, array_column($decodedDataPro['article'], 'id'))) {
            return response('failed store article', 403);
        }

        // get params
        $art_id         = $request->id;
        $title          = $request->title;
        $category       = $request->category;
        $description    = $request->description;
        $status         = $request->status;
        $created_at     = Date('Y-m-d H:i:s');
        $updated_at     = Date('Y-m-d H:i:s');

        // setup row data store json article fields
        $article_data = [
            'id'    => $art_id,
            'ttl'   => $title,
            'ctg'   => $category,
            'dsc'   => $description,
            'stt'   => $status,
            'cat'   => $created_at,
            'uat'   => $updated_at
        ];

        // add row data into field article
        $decodedDataPro['article'][] = $article_data;
        $article = json_encode($decodedDataPro);

        // store profile field file json
        $filePathPro = $folderPathPro . $id . '.json';
        $update_json = file_put_contents($filePathPro, $article);

        if (!$update_json) {
            return response('failed store article', 403);
        }

        return response('success store article', 200);
    }

    public function article_update(Request $request, $id)
    {
        // get data profile from json file
        $folderPathPro = realpath(__DIR__ . '/../../../') . '/public/json/profile/';

        // get file json
        $jsonFilePro = glob($folderPathPro . $id . '.json');

        if ($jsonFilePro === false) {
            return response([], 404);
        }

        $jsonDataPro = file_get_contents($jsonFilePro[0]);
        $decodedDataPro = json_decode($jsonDataPro, true);

        if ($decodedDataPro === null) {
            return response([], 403);
        }

        // check id article
        if (!in_array($request->id, array_column($decodedDataPro['article'], 'id'))) {
            return response('failed update article', 403);
        }

        // get params
        $art_id         = $request->id;
        $title          = $request->title;
        $category       = $request->category;
        $description    = $request->description;
        $status         = $request->status;
        $created_at     = $request->created_at;
        $updated_at     = Date('Y-m-d H:i:s');

        // setup row data store json article fields
        $article_data = [
            'id'    => $art_id,
            'ttl'   => $title,
            'ctg'   => $category,
            'dsc'   => $description,
            'stt'   => $status,
            'cat'   => $created_at,
            'uat'   => $updated_at
        ];

        // update row data in article by id
        foreach ($decodedDataPro['article'] as &$item) {
            if ($item['id'] === $article_data['id']) {
                $item = $article_data;
                break;
            }
        }
        $article = json_encode($decodedDataPro);

        // update profile field file json
        $filePathPro = $folderPathPro . $id . '.json';
        $update_json = file_put_contents($filePathPro, $article);

        if (!$update_json) {
            return response('failed update article', 403);
        }

        return response('success update article', 200);
    }
}
