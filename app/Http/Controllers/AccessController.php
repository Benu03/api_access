<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\UserModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\Register;
use Illuminate\Support\Carbon;

class AccessController extends Controller
{
    public function Register(Request $request)
    {  
        Log::info('Begin Register');
            // Validasi input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:sso.auth.auth_users,email',
            'fullname' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'kota' => 'required|string|max:255',
            'kecamatan' => 'required|string|max:255',
            'kelurahan' => 'required|string|max:255',
            'alamat' => 'required|string|max:500',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'success' => false,
                'message' => 'Validation Failed',
                'errors' => $validator->errors()
            ], 400);
        }

        // Buat username
        $string = strtolower($request->fullname);
        $words = explode(" ", $string);
        $firstWord = str_replace("'", "", $words[0]);
        $secondWord = count($words) > 1 ? substr($words[1], 0, 1) : substr($words[0], 0, 1);
        
        $username = $firstWord . '.' . $secondWord . rand(100, 999);
        $username = str_replace(['-', '(', ')', '..'], '', $username);

        // Pastikan username unik
        while (DB::connection('sso')->table('auth.auth_users')->where('username', $username)->exists()) {
            $username = $firstWord . '.' . $secondWord . rand(100, 999);
        }

        try {
            // Insert ke auth_users
            $dataUser = [
                'username' => $username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_confirm' => false,
                'device_id' => Str::random(8),
                'created_by' => $username,
                'is_active' => true,
                'nik' => $username,
                'fullname' => strtolower($request->fullname),
                'email' => $request->email,
                'address' => $request->alamat, // Perbaikan
                'phone' => $request->phone,
                'wa_number' => $request->phone ?? null, // Gunakan null jika tidak ada
                'auth_type_id' => $request->type_id ?? null,
                'auth_entity_id' => $request->entity_id ?? null,
                'created_by' => $username,
                'auth_mst_division_id' => $request->division_id ?? null,
                'auth_mst_department_id' => $request->department_id ?? null,
                'auth_mst_position_id' => $request->position_id ?? null
            ];

            $userId = DB::connection('sso')->table('auth.auth_users')->insertGetId($dataUser);             

                    $interval = Carbon::now()->addHour()->format('Y-m-d H:i:s'); // Tambah 1 jam


                    $param = http_build_query([
                        'interval' => $interval,
                        'username' => $username
                    ]);
                    
               
                    $paramEncoded = base64_encode($param);
                    
                    $url_verification  = url('api/auth/verification/' . $paramEncoded);
                    
                    $datamail = [
                        'username' => $username,
                        'email' => $request->email,
                        'fullname' => $request->fullname,
                        'url' => $url_verification
                    ];
                
                try {
                    Mail::to($request->email)->send(new Register($datamail));
                } catch (\Exception $e) {
                    Log::error("Failed to send email: " . $e->getMessage());
                }


            if ($request->filled('module') && $request->filled('role')) {
                $checkUserModule = UserModel::GetModuleRoleId($request->module, $request->role);    

                Log::info('Module: ' . $request->module . ', Role: ' . $request->role);

                Log::info($checkUserModule->role_module_id);

                if ($checkUserModule) {
                    $dataAccess = [
                        'auth_users_id' => $userId,
                        'auth_role_module_id' => $checkUserModule->role_module_id,
                        'created_by' => $username
                    ];
                    UserModel::InserAccessModuleRole($dataAccess);
                }
            }

            Log::info('End User Insert');

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'User Success Insert',
                'data' => []
            ], 200);

        } catch (\Exception $e) {
            Log::channel('slack_sso')->critical($e->getMessage());
            return response()->json([
                'status' => 400,
                'success' => false,
                'message' => 'Request Failed',
                'data' => [$e->getMessage()]
            ], 400);
        }
    }


    public function Verification($data)
{
    Log::info('Begin Verification');

    $decodedData = base64_decode($data);
    Log::info('Decoded Data: ' . $decodedData);

    parse_str($decodedData, $params);
    
    Log::info('Parsed Parameters:', $params);

    if (isset($params['interval']) && isset($params['username'])) {
        Log::info('Interval: ' . $params['interval']);
        Log::info('Username: ' . $params['username']);

        // Konversi interval menjadi waktu Unix timestamp
        $intervalTime = strtotime($params['interval']);
        $currentTime = time();

        if ($currentTime < $intervalTime) {
            Log::warning('Verifikasi expired');

            return response()->make(view('verification_expired', [
                'message' => 'Verifikasi Anda sudah expired. Halaman ini akan ditutup dalam 30 detik.'
            ]))->header('Refresh', '30;url=about:blank');
        }

        DB::connection('sso')->table('auth.auth_users')
            ->where('username', $params['username'])
            ->update(['is_confirm' => true]);

        Log::info('Verifikasi berhasil, user dikonfirmasi');

        // return redirect()->away('myapp://verification/success');


        return redirect('https://ts3.co.id/');

        
    } else {
        Log::warning('Parameter tidak lengkap');
        return response()->json(['error' => 'Parameter tidak lengkap'], 400);
    }

    Log::info('End Verification');
}



    
}