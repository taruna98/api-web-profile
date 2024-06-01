<?php

namespace App\Http\Controllers;

use App\Mail\BrevoMail;
use App\Services\BrevoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ProfileController extends Controller
{
    protected $brevoService;

    public function __construct()
    {
        // $this->middleware('auth');

        // function kretech generate code profile
        function generateCode($length = 11)
        {
            $characters = 'abcdefghijklmnopqrstuvwxyz';
            $code = '';

            // generate the first character (3 or 4)
            $firstChar = (string) rand(3, 4);
            $code .= $firstChar;

            // generate the second and third characters (uppercase letters)
            $code .= chr(rand(65, 90)); // uppercase letter
            $code .= chr(rand(65, 90)); // uppercase letter

            // generate the rest of the characters
            $max = strlen($characters) - 1;
            for ($i = 0; $i < $length - 3; $i++) {
                $code .= $characters[rand(0, $max)];
            }

            return $code;
        }
    }

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

    public function is_online($site = 'https://youtube.com/') // change youtube to our website
    {
        if (@fopen($site, "r")) {
            return true;
        } else {
            return false;
        }
    }

    public function request_mail(Request $request, BrevoService $brevoService)
    {
        // call function request with method post
        $response = $this->request($request, $brevoService);

        return $response;
    }

    public function request(Request $request, BrevoService $brevoService)
    {
        // this function for validation email and get status 1 for send email and waiting approval, 2 for process generate, -2 not generate, 3 success registration, -3 failed registration

        $validator = Validator::make($request->all(), [
            'email'     => 'required|email',
            'status'    => 'required'
        ]);

        if (!$this->is_online()) {
            return response('connection timed out', 522);
        }

        if ($validator->fails()) {
            return response('not acceptable', 406);
        }

        // declare variable
        $email          = $request->email;
        $status         = $request->status;
        $url_send       = env('APP_URL') . '/profile/request/mail/?email=' . $email . '&status=' . 2;
        $url_redirect   = env('WEB_COMPANY_URL') . '/?102';
        $timezone       = 'Asia/Jakarta';

        // email validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response('expectation failed', 417);
        }

        // check email exists in system (table users), if exists return status already exists
        $check_email = DB::connection('mysql2')->table('users')->where('email', $email)->first();
        if ($check_email !== null) {
            return response('user already exists', 208);
        }

        // status : 1 = email send to user and waiting response from user, 2 = user click verified link and waiting response from admin, -2 = user not verified link, 3 = admin approved, -3 = admin not approved

        if ($status == '1') { // set status by user from registration
            // return 'if status 1, send message to that email (click link for verification), save to table task opsadmin waiting for accept by admin and set status 1';

            // check from table user_requests where status 1
            $check_user_requests = DB::connection('mysql2')->table('user_requests')->where('email', $email)->where('status', 1)->first();

            if ($check_user_requests) {
                $created_at = $check_user_requests->created_at;
                $date_time_created = Carbon::parse($created_at, $timezone);
                $now = Carbon::now($timezone);
                $now_min_3_days = $now->copy()->subDays(3);
                $date_time_created_plus3_days = $date_time_created->copy()->addDays(3);

                // check last register
                if ($date_time_created > $now_min_3_days) {
                    // send email to user for click link or waiting
                    $to         = $email;
                    $subject    = 'Kretech - Register Web Portfolio';
                    $body       = '<html><body><h2>Hi, ' . explode('@', $email)[0] . '</h2><h3>Anda telah melakukan registrasi untuk membuat Website Portfolio ini sebelumnya. Silahkan cek email Anda atau silahkan registrasi ulang setelah tanggal ' . $date_time_created_plus3_days . ' dan nantikan informasi selanjutnya.</h3><h3>Salam Hormat, <br><br>Kretech Team</h3></body></html>';

                    // return 'email to : ' . $email . ', subject : ' . $subject . ', body : ' . $body . ', register again at : ' . $date_time_created_plus3_days;

                    $sendEmail  = $brevoService->sendEmail($to, $subject, $body);
                    if (!$sendEmail) {
                        return response('precondition failed', 412);
                    } else {
                        return response('email notification send', 200);
                    }
                } else {
                    // update status to -2 is user not verified link
                    $update_user_request = DB::connection('mysql2')->table('user_requests')
                        ->where('email', $email)
                        ->where('status', 1)
                        ->update(['status' => -2]);
                    if (!$update_user_request) {
                        return response('precondition failed', 412);
                    }

                    $update_tasking = DB::connection('mysql2')->table('tasking')
                        ->where('id', $check_user_requests->task_id)
                        ->update(['status' => -2]);
                    if (!$update_tasking) {
                        return response('precondition failed', 412);
                    }
                }
            }

            // check from table user_requests where status 2
            $check_user_requests_2 = DB::connection('mysql2')->table('user_requests')->where('email', $email)->where('status', 2)->first();
            if ($check_user_requests_2) {
                return response('email notification send', 200);
            }

            // send email to user
            $to         = $email;
            $subject    = 'Kretech - Register Web Portfolio';
            $body       = '<html><body><h4>Hi, ' . explode('@', $email)[0] . '</h4><p>Terimakasih telah melakukan registrasi untuk membuat Website Portfolio ini. Silahkan klik <a href="' . $url_send . '" target="_blank"><i>disini</i></a> untuk melakukan Verifikasi dan nantikan informasi selanjutnya dalam 3 Hari kedepan.</p><p>Salam Hormat, <br><br>Kretech Team</p></body></html>';

            // return 'email to : ' . $email . ', subject : ' . $subject . ', body : ' . $body . ', url_send : ' . $url_send;

            $sendEmail  = $brevoService->sendEmail($to, $subject, $body);
            if (!$sendEmail) {
                return response('precondition failed', 412);
            }

            // tasking : user_id = 0, module = Kretech, scene = Register, task = Web Profile, admin_id = 0, status = 1
            $task_user_id   = 0;
            $task_module    = 'Kretech';
            $task_scene     = 'Register';
            $task_task      = 'Web Profile';
            $task_admin_id  = 0;
            $task_status    = 1;

            // $task_temp = [
            //     'task_user_id'   => $task_user_id,
            //     'task_module'    => $task_module,
            //     'task_scene'     => $task_scene,
            //     'task_task'      => $task_task,
            //     'task_admin_id'  => $task_admin_id,
            //     'task_status'    => $task_status
            // ];

            $insert_tasking = DB::connection('mysql2')->table('tasking')->insertGetId([
                'user_id'   => $task_user_id,
                'module'    => $task_module,
                'scene'     => $task_scene,
                'task'      => $task_task,
                'admin_id'  => $task_admin_id,
                'status'    => $task_status
            ]);
            if (!$insert_tasking) {
                return response('precondition failed', 412);
            }

            // user_requests : task_id = id, module = Kretech, email = user@email.com, status = 1
            $user_req_task_id   = $insert_tasking;
            $user_req_module    = 'Kretech';
            $user_req_email     = $email;
            $user_req_status    = 1;

            // $user_req_temp = [
            //     'user_req_task_id'   => $user_req_task_id,
            //     'user_req_module'    => $user_req_module,
            //     'user_req_email'     => $user_req_email,
            //     'user_req_status'    => $user_req_status
            // ];

            $insert_user_requests = DB::connection('mysql2')->table('user_requests')->insert([
                'task_id'   => $user_req_task_id,
                'module'    => $user_req_module,
                'email'     => $user_req_email,
                'status'    => $user_req_status
            ]);
            if (!$insert_user_requests) {
                return response('precondition failed', 412);
            }

            return response('email send', 200);
        } else if ($status == '2') { // set status by user from verification email
            // return 'if status 2, update status to 2 in table tasking and user_requests to waiting approval from admin then redirect user to page waiting to approve by admin';

            // get from table user_requests
            $get_user_requests = DB::connection('mysql2')->table('user_requests')->where('email', $email)->where('status', 1)->first();
            if (!$get_user_requests) {
                $get_user_requests_2 = DB::connection('mysql2')->table('user_requests')->where('email', $email)->where('status', 2)->first();
                if ($get_user_requests_2) {
                    // link already clicked
                    return response('link already clicked', 200);
                }
            }

            // update status to 2 in table tasking user verified link
            $update_user_request = DB::connection('mysql2')->table('user_requests')
                ->where('email', $email)
                ->where('status', 1)
                ->update(['status' => 2]);
            if (!$update_user_request) {
                return response('precondition failed', 412);
            }

            // update status to 2 in table user_rquests user verified link
            $update_tasking = DB::connection('mysql2')->table('tasking')
                ->where('id', $get_user_requests->task_id)
                ->update(['status' => 2]);
            if (!$update_tasking) {
                return response('precondition failed', 412);
            }

            // redirect to page waiting
            // return response('redirect to page waiting', 200);
            return redirect($url_redirect);
        } else if ($status == '3') { // set status by admin from opsadmin
            // return 'if status 3, set status 3 (approved), create profile user in table users opsadmin, create user in api table profiles include generate code, hit api for store (create json file in api project), send message to that email (success register), set status 3 (success)';

            // get params
            $code               = generateCode();
            // ---
            $opsadmin_url       = 'http://127.0.0.1:8002/';
            $opsadmin_pass      = '123456';
            $web_profile_url    = 'http://localhost/web_profile_v1/?proid/' . $code;

            // get from table user_requests
            $get_user_requests = DB::connection('mysql2')->table('user_requests')->where('email', $email)->where('status', 2)->first();

            // update status to 3 in table tasking admin approved user request
            $update_user_request = DB::connection('mysql2')->table('user_requests')
                ->where('email', $email)
                ->where('status', 2)
                ->update(['status' => 3]);
            if (!$update_user_request) {
                return response('precondition failed', 412);
            }

            // update status to 3 in table user_rquests admin approved user request
            $update_tasking = DB::connection('mysql2')->table('tasking')
                ->where('id', $get_user_requests->task_id)
                ->update(['status' => 3]);
            if (!$update_tasking) {
                return response('precondition failed', 412);
            }

            // create user in api table profiles include generate code
            $profile_create = DB::connection('mysql')->table('profiles')->insert([
                'cod'   => $code,
                'eml'   => $email
            ]);
            if (!$profile_create) {
                return response('precondition failed', 412);
            }

            // hit api for store (create json file in api project)
            $response = $this->store($code); // call function store with method post
            if ($response == 'failed create file') {
                return response('precondition failed', 412);
            }

            // send message to that email (success register)
            $to         = $email;
            $subject    = 'Kretech - Register Web Portfolio';
            $body       = '<html><body><h4>Selamat, ' . explode('@', $email)[0] . '</h4><br><p>Web Profile Anda berhasil didaftarkan. Silahkan akses melalui link berikut,</p><p>Web Url : <a href="' . $web_profile_url . '" target"_blank">Profile</a></p><p>CMS (Content Management System) : <a href="' . $opsadmin_url . '" target"_blank">Content Management</a></p></p><p>Password CMS : ' . $opsadmin_pass . '</p><br><p>Salam Hormat, <br><br>Kretech Team</p></body></html>';
            // return 'email to : ' . $email . ', subject : ' . $subject . ', body : ' . $body . ', web url : ' . $web_profile_url . ', cms url : ' . $opsadmin_url . ', pass cms : ' . $opsadmin_pass;
            $sendEmail = $brevoService->sendEmail($to, $subject, $body);
            if (!$sendEmail) {
                return response('precondition failed', 412);
            }

            // return success response
            return response('success register user', 201);
        } else if ($status == '-3') { // set status by admin from opsadmin
            // return 'if status -3, set status -3 (rejected), send message to that email (rejected register)';

            // get from table user_requests
            $get_user_requests = DB::connection('mysql2')->table('user_requests')->where('email', $email)->where('status', 2)->first();

            // update status to -3 in table tasking admin rejected user request
            $update_user_request = DB::connection('mysql2')->table('user_requests')
                ->where('email', $email)
                ->where('status', 2)
                ->update(['status' => -3]);
            if (!$update_user_request) {
                return response('precondition failed', 412);
            }

            // update status to -3 in table user_rquests admin rejected user request
            $update_tasking = DB::connection('mysql2')->table('tasking')
                ->where('id', $get_user_requests->task_id)
                ->update(['status' => -3]);
            if (!$update_tasking) {
                return response('precondition failed', 412);
            }

            // send message to that email (rejected)
            $to         = $email;
            $subject    = 'Kretech - Register Web Portfolio';
            $body       = '<html><body><h4>Hai, ' . explode('@', $email)[0] . '</h4><br><p>Maaf, Web Profile Anda tidak dapat didaftarkan. Silahkan lakukan registrasi ulang, Terima Kasih.</p><br><p>Salam Hormat, <br><br>Kretech Team</p></body></html>';
            // return 'email to : ' . $email . ', subject : ' . $subject . ', body : ' . $body;
            $sendEmail = $brevoService->sendEmail($to, $subject, $body);
            if (!$sendEmail) {
                return response('precondition failed', 412);
            }

            // return success response
            return response('rejected user', 200);
        }
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
        $name       = preg_match('/^[^._\d]+/', $email, $matches) ? ucfirst($matches[0]) : 'User';
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
                "sci": "fas fa-cube|fas fa-cube|fas fa-cube",
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
