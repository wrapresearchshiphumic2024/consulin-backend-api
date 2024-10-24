<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use App\Models\Appointment;
use App\Models\AiAnalyzer;
use App\Models\Psychologist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $patients = Patient::with('user')->get();
        return response()->json([
            'status' => 'success',
            'data' => $patients,
        ]);
    }
    /**
     * Display all appointments for the specified patient.
     */
    public function appointments($id)
    {
        $patient = Patient::find($id);
        if (!$patient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Patient not found',
            ], 404);
        }

        $appointments = Appointment::where('patient_id', $patient->id)->get();
        //find psychologist and patient details
        $appointments->transform(function ($appointment) {
            return [
                'id' => $appointment->id,
                'date' => $appointment->date,
                'time' => $appointment->time,
                'status' => $appointment->status,
                'psychologist' => [
                    'id' => $appointment->psychologist->id,
                    'name' => $appointment->psychologist->user->firstname . ' ' . $appointment->psychologist->user->lastname,
                    'email' => $appointment->psychologist->user->email,
                    'phone_number' => $appointment->psychologist->user->phone_number,
                ],
            ];
        });
        return response()->json([
            'status' => 'success',
            'data' => $appointments,
        ]);
    }

    /**
     * Display All Psychologosts.
     */
    public function psychologist()
    {
        $psychologists = Psychologist::with('user')->where('is_verified', true)->get();

        $psychologists->transform(function ($psychologist) {
            return [
                'id' => $psychologist->id,
                'profile_picture' => $psychologist->user->profile_picture,
                'firstname' => $psychologist->user->firstname,
                'lastname' => $psychologist->user->lastname,
                'profesional_identification_number' => $psychologist->profesional_identification_number,
                'degree' => $psychologist->degree,
                'specialization' => $psychologist->specialization,
                'work_experience' => $psychologist->work_experience,
                'is_verified' => $psychologist->is_verified,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'List of Psychologists',
            'data' => $psychologists,
        ], 200);
    }


    /**
     * Display AI analysis results for the specified patient.
     */
    public function aiAnalysis($id)
    {
        $patient = Patient::find($id);
        if (!$patient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Patient not found',
            ], 404);
        }

        $analysis = AiAnalyzer::where('patient_id', $patient->id)->get();
        return response()->json([
            'status' => 'success',
            'data' => $analysis,
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $patient = Patient::with('user')->find($id);
        if (!$patient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Patient not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $patient,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $patient = Patient::find($id);
        if (!$patient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Patient not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'firstname' => 'sometimes|string',
            'lastname' => 'sometimes|string',
            'email' => 'sometimes|string|email|unique:users,email,' . $patient->user_id,
            'password' => 'sometimes|string|min:8',
            'phone_number' => 'sometimes|string',
            'gender' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update user data
        $user = User::find($patient->user_id);
        if ($request->has('firstname')) $user->firstname = $request->firstname;
        if ($request->has('lastname')) $user->lastname = $request->lastname;
        if ($request->has('email')) $user->email = $request->email;
        if ($request->has('password')) $user->password = Hash::make($request->password);
        if ($request->has('phone_number')) $user->phone_number = $request->phone_number;
        if ($request->has('gender')) $user->gender = $request->gender;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Patient updated successfully',
            'data' => $patient,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $patient = Patient::find($id);
        if (!$patient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Patient not found',
            ], 404);
        }

        // Delete associated user
        $user = User::find($patient->user_id);
        if ($user) {
            $user->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Patient deleted successfully',
        ]);
    }
}
