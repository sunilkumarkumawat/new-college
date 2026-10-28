<?php

namespace App\Http\Controllers\student_login;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\StudentComplaint;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Redirect;
use Session;

class StudentComplaintController extends Controller
{
    private function isStudent()
    {
        return Session::get('role_id') == 3;
    }

    // Student: apni complaints dekhe + nayi complaint daale
    // Admin/Staff: sabhi students ki complaints dekhe
    public function studentComplaints(Request $request)
    {
        if ($this->isStudent() && $request->isMethod('post')) {
            $request->validate([
                'subject'     => 'required|string|max:255',
                'description' => 'required|string|max:5000',
            ]);

            $student = Admission::find(Session::get('id'));

            $add = new StudentComplaint;
            $add->session_id    = Session::get('session_id');
            $add->branch_id     = Session::get('branch_id');
            $add->admission_id  = Session::get('id');
            $add->class_type_id = $student->class_type_id ?? Session::get('class_type_id');
            $add->section_id    = $student->section_id ?? null;
            $add->subject       = $request->subject;
            $add->description   = $request->description;
            $add->date          = date('Y-m-d');
            $add->status        = 'pending';
            $add->save();

            return Redirect::to('student_complaints')->with('message', 'Complaint submitted successfully.');
        }

        $query = StudentComplaint::with('Admission', 'ClassType')
            ->where('session_id', Session::get('session_id'))
            ->where('branch_id', Session::get('branch_id'));

        if ($this->isStudent()) {
            // Student ko sirf apni daali hui complaints
            $query->where('admission_id', Session::get('id'));
        }

        $counts = (clone $query)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        if ($this->isStudent()) {
            $data = $query->orderBy('id', 'DESC')->get();
            return view('students.student_complaint.index', ['data' => $data, 'counts' => $counts]);
        }

        if ($request->filled('status') && array_key_exists($request->status, StudentComplaint::STATUSES)) {
            $query->where('status', $request->status);
        }
        $data = $query->orderBy('id', 'DESC')->get();

        return view('students.student_complaint.complaint', ['data' => $data, 'counts' => $counts]);
    }

    // Admin: reply / status update
    public function reply(Request $request)
    {
        if ($this->isStudent()) {
            abort(403);
        }

        $request->validate([
            'complaint_id' => 'required|integer',
            'status'       => ['required', Rule::in(array_keys(StudentComplaint::STATUSES))],
            'admin_reply'  => 'nullable|string|max:5000',
        ]);

        $complaint = StudentComplaint::where('branch_id', Session::get('branch_id'))->findOrFail($request->complaint_id);
        $complaint->status      = $request->status;
        $complaint->admin_reply = $request->admin_reply;
        $complaint->replied_by  = Session::get('id');
        $complaint->replied_at  = now();
        $complaint->save();

        return Redirect::to('student_complaints')->with('message', 'Complaint updated successfully.');
    }

    // Student: admin ka reply aane tak apni complaint edit kar sakta hai
    public function update(Request $request)
    {
        if (!$this->isStudent()) {
            abort(403);
        }

        $request->validate([
            'complaint_id' => 'required|integer',
            'subject'      => 'required|string|max:255',
            'description'  => 'required|string|max:5000',
        ]);

        $complaint = StudentComplaint::where('branch_id', Session::get('branch_id'))
            ->where('admission_id', Session::get('id'))
            ->find($request->complaint_id);

        if (!$complaint || !$complaint->canStudentEdit()) {
            return Redirect::to('student_complaints')->with('error', 'Admin has already replied, complaint cannot be edited.');
        }

        $complaint->subject     = $request->subject;
        $complaint->description = $request->description;
        $complaint->save();

        return Redirect::to('student_complaints')->with('message', 'Complaint updated successfully.');
    }

    // Student apni complaint (admin reply se pehle) delete kar sakta hai, admin koi bhi
    public function delete(Request $request)
    {
        $query = StudentComplaint::where('branch_id', Session::get('branch_id'));

        if ($this->isStudent()) {
            $query->where('admission_id', Session::get('id'));
        }

        $complaint = $query->find($request->delete_id);
        if (!$complaint || ($this->isStudent() && !$complaint->canStudentEdit())) {
            return Redirect::to('student_complaints')->with('error', 'Complaint not found.');
        }
        $complaint->delete();

        return Redirect::to('student_complaints')->with('message', 'Complaint deleted successfully.');
    }
}