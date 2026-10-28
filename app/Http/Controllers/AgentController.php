<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Agent;
use App\Models\Admission;
use App\Models\Enquiry;
use Session;
use Helper;
use Redirect;

class AgentController extends Controller
{
    /**
     * Display listing of agents with Executive theme layout
     */
    public function index(Request $request)
    {
        $branchId = Session::get('admin_branch_id') ?: Session::get('branch_id');
        $search = [
            'name' => $request->name ?? '',
            'mobile' => $request->mobile ?? '',
            'status' => $request->status ?? '',
        ];

        $query = Agent::withCount(['Admissions', 'Enquiries'])->whereNull('deleted_at');

        if (!empty($branchId)) {
            $query->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            });
        }

        if (!empty($search['name'])) {
            $query->where('name', 'like', '%' . $search['name'] . '%');
        }
        if (!empty($search['mobile'])) {
            $query->where('mobile', 'like', '%' . $search['mobile'] . '%');
        }
        if ($search['status'] !== '' && $search['status'] !== null) {
            $query->where('status', (int)$search['status']);
        }

        $agents = $query->orderBy('name', 'ASC')->paginate(25)->appends($search);

        // KPI Counts
        $totalAgents = Agent::whereNull('deleted_at')->count();
        $activeAgents = Agent::where('status', 1)->whereNull('deleted_at')->count();
        $totalAdmissionsViaAgents = Admission::whereNotNull('agent_id')->where('agent_id', '>', 0)->whereNull('deleted_at')->count();
        $totalEnquiriesViaAgents = Enquiry::whereNotNull('agent_id')->where('agent_id', '>', 0)->whereNull('deleted_at')->count();

        return view('agents.index', compact(
            'agents',
            'search',
            'totalAgents',
            'activeAgents',
            'totalAdmissionsViaAgents',
            'totalEnquiriesViaAgents'
        ));
    }

    /**
     * Store or update an agent
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'mobile' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
        ]);

        $branchId = Session::get('admin_branch_id') ?: Session::get('branch_id');
        $userId = Session::get('id');
        $sessionId = Session::get('session_id');

        $agent = !empty($request->id) ? Agent::find($request->id) : new Agent;
        if (!$agent) {
            return redirect()->back()->with('error', 'Agent record not found.');
        }

        $agent->name = trim($request->name);
        $agent->mobile = trim($request->mobile ?? '');
        $agent->email = trim($request->email ?? '');
        $agent->address = trim($request->address ?? '');
        $agent->status = $request->has('status') ? (int)$request->status : 1;

        if (empty($agent->id)) {
            $agent->branch_id = $branchId;
            $agent->user_id = $userId;
            $agent->session_id = $sessionId;
        }

        $agent->save();

        $msg = !empty($request->id) ? 'Agent updated successfully!' : 'Agent added successfully!';
        return redirect()->back()->with('message', $msg);
    }

    /**
     * AJAX: Quick Add Agent from Admission / Enquiry pages
     */
    public function quickAddAgent(Request $request)
    {
        $name = trim($request->name ?? '');
        if (empty($name)) {
            return response()->json([
                'status' => false,
                'message' => 'Agent Name is required.'
            ], 422);
        }

        $branchId = Session::get('admin_branch_id') ?: Session::get('branch_id');
        $userId = Session::get('id');
        $sessionId = Session::get('session_id');

        // Check if agent already exists with exact name and branch
        $existing = Agent::where('name', $name)->whereNull('deleted_at');
        if (!empty($branchId)) {
            $existing->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            });
        }
        $existing = $existing->first();

        if ($existing) {
            return response()->json([
                'status' => true,
                'is_existing' => true,
                'message' => 'Agent already exists.',
                'data' => [
                    'id' => $existing->id,
                    'name' => $existing->name,
                    'mobile' => $existing->mobile,
                ]
            ]);
        }

        $agent = new Agent;
        $agent->name = $name;
        $agent->mobile = trim($request->mobile ?? '');
        $agent->email = trim($request->email ?? '');
        $agent->address = trim($request->address ?? '');
        $agent->status = 1;
        $agent->branch_id = $branchId;
        $agent->user_id = $userId;
        $agent->session_id = $sessionId;
        $agent->save();

        return response()->json([
            'status' => true,
            'is_existing' => false,
            'message' => 'Agent created successfully!',
            'data' => [
                'id' => $agent->id,
                'name' => $agent->name,
                'mobile' => $agent->mobile,
            ]
        ]);
    }

    /**
     * Delete an agent (Soft delete)
     */
    public function delete(Request $request)
    {
        $agent = Agent::find($request->id);
        if ($agent) {
            $agent->delete();
            return redirect()->back()->with('message', 'Agent deleted successfully.');
        }
        return redirect()->back()->with('error', 'Agent not found.');
    }

    /**
     * Toggle status of an agent
     */
    public function status(Request $request)
    {
        $agent = Agent::find($request->id);
        if ($agent) {
            $agent->status = $agent->status == 1 ? 0 : 1;
            $agent->save();
            return response()->json([
                'status' => true,
                'new_status' => $agent->status,
                'message' => 'Agent status updated successfully.'
            ]);
        }
        return response()->json(['status' => false, 'message' => 'Agent not found.'], 404);
    }
}
