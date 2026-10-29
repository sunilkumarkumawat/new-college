<?php

namespace App\Http\Controllers\CollegeFees;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Models\Admission;
use App\Models\ClassType;
use App\Models\Setting;
use App\Models\User;
use App\Models\Master\Course;
use App\Models\CollegeFees\CollegeFeeStructure;
use App\Models\CollegeFees\CollegeFeeAssignment;
use App\Models\CollegeFees\CollegeFeeReceipt;
use App\Models\CollegeFees\CollegeFeeReceiptItem;
use App\Models\CollegeFees\CollegeFeeHead;
use App\Helpers\helper;

class CollegeFeeController extends Controller
{
    /**
     * Helper to detect semester number from class name
     */
    protected function parseSemesterNumber($className)
    {
        if (preg_match('/(\d+)(?:st|nd|rd|th)?\s*(?:sem|year)/i', $className, $m)) {
            return (int)$m[1];
        }
        return 1;
    }

    /**
     * Get active branch ID from session
     */
    protected function getBranchId()
    {
        return Session::get('branch_id') ?: 1;
    }

    /**
     * 1. Dashboard
     */
    public function dashboard(Request $request)
    {
        $branchId = $this->getBranchId();

        $totalStudents = Admission::where('branch_id', $branchId)
            ->where('status', 1)
            ->where('school', 1)
            ->count();

        $boysCount = Admission::where('branch_id', $branchId)->where('status', 1)->where('school', 1)->where('gender_id', 1)->count();
        $girlsCount = Admission::where('branch_id', $branchId)->where('status', 1)->where('school', 1)->whereIn('gender_id', [2, 3])->count();

        $totalAssigned = (float)CollegeFeeAssignment::where('branch_id', $branchId)->whereNull('deleted_at')->sum('gross_amount');
        if ($totalAssigned <= 0) {
            $totalAssigned = (float)CollegeFeeAssignment::where('branch_id', $branchId)->whereNull('deleted_at')->sum('net_amount');
        }
        $totalCollected = (float)CollegeFeeReceipt::where('branch_id', $branchId)->whereNull('deleted_at')->sum('amount_paid');
        $totalDues = (float)CollegeFeeAssignment::where('branch_id', $branchId)->whereNull('deleted_at')->sum('due_amount');
        $todayCollected = (float)CollegeFeeReceipt::where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->whereDate('payment_date', date('Y-m-d'))
            ->sum('amount_paid');
        $todayReceiptsCount = CollegeFeeReceipt::where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->whereDate('payment_date', date('Y-m-d'))
            ->count();

        // Branch & Session details
        $branch = \App\Models\Master\Branch::find($branchId);
        $dynamicSession = helper::resolveCurrentFinancialSession($branchId);

        // Course summary for this branch
        $courses = Course::where('branch_id', $branchId)->orderBy('name', 'ASC')->get();
        if ($courses->isEmpty()) {
            $courses = Course::orderBy('name', 'ASC')->get();
        }

        // Distinct Batches
        $batches = Admission::where('branch_id', $branchId)
            ->whereNotNull('batch')
            ->where('batch', '!=', '')
            ->distinct()
            ->orderBy('batch', 'DESC')
            ->pluck('batch')
            ->toArray();
        if (empty($batches)) {
            $batches = ['2026', '2025', '2024'];
        }

        // Pre-fetch enrolled student counts per course & batch
        $enrolledCounts = DB::table('admissions')
            ->where('branch_id', $branchId)
            ->where('status', 1)
            ->where('school', 1)
            ->select('course', 'batch', DB::raw('COUNT(*) as total'))
            ->groupBy('course', 'batch')
            ->get()
            ->keyBy(function($item) {
                return strtoupper(trim($item->course)) . '_' . trim($item->batch);
            });

        // Pre-fetch assignments aggregated by course_id and batch
        $assignmentAgg = DB::table('college_fee_assignments as a')
            ->join('admissions as s', 'a.student_id', '=', 's.id')
            ->where('a.branch_id', $branchId)
            ->whereNull('a.deleted_at')
            ->select(
                'a.course_id',
                's.batch',
                DB::raw('SUM(a.gross_amount) as total_demand'),
                DB::raw('SUM(a.paid_amount) as total_paid'),
                DB::raw('SUM(a.due_amount) as total_due'),
                DB::raw('COUNT(DISTINCT CASE WHEN a.due_amount > 0 THEN s.id END) as defaulters_count')
            )
            ->groupBy('a.course_id', 's.batch')
            ->get()
            ->keyBy(function($item) {
                return $item->course_id . '_' . trim($item->batch);
            });

        // Pre-fetch receipts aggregated by course_id and batch (active only)
        $receiptAgg = DB::table('college_fee_receipts as r')
            ->join('admissions as s', 'r.student_id', '=', 's.id')
            ->where('r.branch_id', $branchId)
            ->whereNull('r.deleted_at')
            ->select(
                'r.course_id',
                's.batch',
                DB::raw('SUM(r.amount_paid) as total_collected')
            )
            ->groupBy('r.course_id', 's.batch')
            ->get()
            ->keyBy(function($item) {
                return $item->course_id . '_' . trim($item->batch);
            });

        // Build Course-Batch stats covering ALL courses and ALL batches
        $courseBatchStats = [];
        $progressionReadyCount = 0;
        foreach ($courses as $c) {
            foreach ($batches as $b) {
                $keyEnrolled = strtoupper(trim($c->name)) . '_' . trim($b);
                $enrolled = isset($enrolledCounts[$keyEnrolled]) ? (int)$enrolledCounts[$keyEnrolled]->total : 0;

                $keyAssign = $c->id . '_' . trim($b);
                $assignRow = $assignmentAgg[$keyAssign] ?? null;

                $demand = $assignRow ? (float)$assignRow->total_demand : 0;
                $paid = $assignRow ? (float)$assignRow->total_paid : 0;
                $dues = $assignRow ? (float)$assignRow->total_due : 0;
                $defaulters = $assignRow ? (int)$assignRow->defaulters_count : 0;

                $recRow = $receiptAgg[$keyAssign] ?? null;
                $collected = $recRow ? (float)$recRow->total_collected : $paid;

                // ONLY show courses & batches where fees are assigned (demand > 0 or collected > 0)
                if ($demand > 0 || $collected > 0) {
                    $pct = $demand > 0 ? min(100, round(($collected / $demand) * 100, 1)) : 0;
                    $courseBatchStats[] = [
                        'course_id' => $c->id,
                        'course_name' => $c->name,
                        'total_semester' => $c->total_semester ?: 6,
                        'batch' => $b,
                        'enrolled' => $enrolled,
                        'demand' => $demand,
                        'collected' => $collected,
                        'dues' => $dues,
                        'defaulters' => $defaulters,
                        'percent' => $pct
                    ];
                    if ($enrolled > 0) {
                        $progressionReadyCount++;
                    }
                }
            }
        }

        // Top Defaulter Course Batches (ranked by highest pending dues DESC)
        $topDefaulterBatches = collect($courseBatchStats)
            ->where('dues', '>', 0)
            ->sortByDesc('dues')
            ->values()
            ->take(6)
            ->all();

        // Total Defaulters count across college
        $defaultersCount = CollegeFeeAssignment::where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->where('due_amount', '>', 0)
            ->distinct('student_id')
            ->count('student_id');

        // Payment mode breakdown (active only)
        $modeBreakdown = CollegeFeeReceipt::where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->select('payment_mode', DB::raw('SUM(amount_paid) as total_amt'), DB::raw('COUNT(*) as total_count'))
            ->groupBy('payment_mode')
            ->get();

        // Clearance Status
        $paidStatuses = CollegeFeeAssignment::where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->select('student_id', DB::raw('SUM(due_amount) as total_due'), DB::raw('SUM(paid_amount) as total_paid'))
            ->groupBy('student_id')
            ->get();

        $fullyPaidCount = 0;
        $partialPaidCount = 0;
        $unpaidCount = 0;
        foreach ($paidStatuses as $ps) {
            if ($ps->total_due == 0 && $ps->total_paid > 0) {
                $fullyPaidCount++;
            } elseif ($ps->total_due > 0 && $ps->total_paid > 0) {
                $partialPaidCount++;
            } else {
                $unpaidCount++;
            }
        }

        // Recent Receipts (active receipts)
        $recentReceipts = CollegeFeeReceipt::with(['student', 'course', 'cashier'])
            ->where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->take(10)
            ->get();

        $activeBatches = collect($courseBatchStats)->pluck('batch')->unique()->values()->all();
        $activeCourseIds = collect($courseBatchStats)->pluck('course_id')->unique()->values()->all();
        $activeCourses = Course::whereIn('id', $activeCourseIds)->orderBy('name', 'ASC')->get();
        if ($activeCourses->isEmpty()) {
            $activeCourses = $courses;
        }

        return view('college_fees.dashboard', compact(
            'totalStudents',
            'boysCount',
            'girlsCount',
            'totalAssigned',
            'totalCollected',
            'totalDues',
            'todayCollected',
            'todayReceiptsCount',
            'branch',
            'dynamicSession',
            'courses',
            'batches',
            'activeBatches',
            'activeCourses',
            'courseBatchStats',
            'progressionReadyCount',
            'topDefaulterBatches',
            'defaultersCount',
            'modeBreakdown',
            'fullyPaidCount',
            'partialPaidCount',
            'unpaidCount',
            'recentReceipts'
        ));
    }

    /**
     * Resolve the active semester for enrolled students in a course & batch.
     * Looks at fee assignments first, then ClassType of enrolled students.
     */
    public function resolveActiveSemester($branchId, $courseId, $batch, $maxSemesters = 6)
    {
        $course = Course::find($courseId);
        $courseName = $course ? $course->name : '';

        // 1. Check if students of this course & batch have any active fee assignments
        $maxAssignSem = CollegeFeeAssignment::where('branch_id', $branchId)
            ->where('course_id', $courseId)
            ->whereHas('student', function($q) use ($courseId, $courseName, $batch) {
                $q->where('status', 1)->where('school', 1);
                if (!empty($batch)) {
                    $q->where('batch', $batch);
                }
                $q->where(function($sub) use ($courseId, $courseName) {
                    $sub->where('course', $courseName)
                        ->orWhereHas('ClassTypes', function($ct) use ($courseId) {
                            $ct->where('course_id', $courseId);
                        });
                });
            })
            ->max('semester_no');

        if ($maxAssignSem && $maxAssignSem >= 1) {
            return min((int)$maxAssignSem, (int)$maxSemesters);
        }

        // 2. Check ClassTypes of enrolled students in this course & batch
        $studentClassTypeIds = Admission::where('branch_id', $branchId)
            ->where('status', 1)
            ->where('school', 1)
            ->where(function($q) use ($courseId, $courseName) {
                $q->where('course', $courseName)
                  ->orWhereHas('ClassTypes', function($sub) use ($courseId) {
                      $sub->where('course_id', $courseId);
                  });
            })
            ->when(!empty($batch), function($q) use ($batch) {
                $q->where('batch', $batch);
            })
            ->pluck('class_type_id')
            ->filter()
            ->unique();

        if ($studentClassTypeIds->isNotEmpty()) {
            $classTypes = ClassType::whereIn('id', $studentClassTypeIds)->get();
            $detectedSems = [];
            foreach ($classTypes as $ct) {
                if (preg_match('/(\d+)/', $ct->name, $m)) {
                    $detectedSems[] = (int)$m[1];
                }
            }
            if (!empty($detectedSems)) {
                return min((int)max($detectedSems), (int)$maxSemesters);
            }
        }

        return 1;
    }

    /**
     * 2. Fee Structures Matrix
     */
    public function structures(Request $request)
    {
        $branchId = $this->getBranchId();

        $courses = Course::where('branch_id', $branchId)->orderBy('name', 'ASC')->get();
        if ($courses->isEmpty()) {
            $courses = Course::orderBy('name', 'ASC')->get();
        }

        $autoOpenStudentId = null;
        $courseId = null;
        $batch = null;
        $semesterNo = null;
        if ($request->has('student_id') && !empty($request->get('student_id'))) {
            $autoOpenStudentId = (int)$request->get('student_id');
            $targetStudent = Admission::find($autoOpenStudentId);
            if ($targetStudent) {
                if (!$request->has('course_id') || empty($request->get('course_id'))) {
                    if (!empty($targetStudent->course_id)) {
                        $courseId = $targetStudent->course_id;
                    } elseif (!empty($targetStudent->ClassTypes->course_id)) {
                        $courseId = $targetStudent->ClassTypes->course_id;
                    } elseif (!empty($targetStudent->course)) {
                        $cFind = Course::where('name', trim($targetStudent->course))->first();
                        if ($cFind) $courseId = $cFind->id;
                    }
                }
                if (!$request->has('batch') || empty($request->get('batch'))) {
                    if (!empty($targetStudent->batch)) {
                        $batch = $targetStudent->batch;
                    }
                }
                if (!$request->has('semester_no') || empty($request->get('semester_no'))) {
                    $stuAssignment = CollegeFeeAssignment::where('student_id', $targetStudent->id)
                        ->whereNull('deleted_at')
                        ->orderBy('semester_no', 'DESC')
                        ->first();
                    if ($stuAssignment && !empty($stuAssignment->semester_no)) {
                        $semesterNo = (int)$stuAssignment->semester_no;
                    } else {
                        $ctName = $targetStudent->ClassTypes->name ?? '';
                        if (preg_match('/(\d+)(?:st|nd|rd|th)?\s*(?:sem|semester)?/i', $ctName, $m)) {
                            $semesterNo = (int)$m[1];
                        }
                    }
                }
            }
        } elseif ($request->has('course') && !$request->has('course_id')) {
            $cByCourse = Course::where('name', trim($request->get('course')))->first();
            if ($cByCourse) {
                $courseId = $cByCourse->id;
            }
        }

        if ($request->has('course_id') && !empty($request->get('course_id'))) {
            $courseId = (int)$request->get('course_id');
        }
        if ($request->has('batch') && !empty($request->get('batch'))) {
            $batch = (string)$request->get('batch');
        }

        $selectedCourse = $courseId ? Course::find($courseId) : null;
        $maxSemesters = $selectedCourse ? ($selectedCourse->total_semester ?: 6) : 6;

        if ($courseId && $batch) {
            if (empty($semesterNo)) {
                if ($request->has('semester_no') && !empty($request->get('semester_no')) && $request->get('semester_no') !== 'auto') {
                    $semesterNo = (int)$request->get('semester_no');
                } else {
                    $semesterNo = $this->resolveActiveSemester($branchId, $courseId, $batch, $maxSemesters);
                }
            }

            $structures = CollegeFeeStructure::where('branch_id', $branchId)
                ->where('course_id', $courseId)
                ->where('semester_no', $semesterNo)
                ->where(function($sub) use ($batch) {
                    $sub->where('batch', $batch)->orWhereNull('batch');
                })
                ->orderBy('id', 'ASC')
                ->get();
        } else {
            $structures = collect([]);
            $semesterNo = null;
        }

        if ($request->ajax() || $request->wantsJson()) {
            $totalBoys = (float)$structures->sum('amount_boys');
            $totalGirls = (float)$structures->sum('amount_girls');
            return response()->json([
                'status' => 'success',
                'course' => $selectedCourse,
                'courseId' => $courseId,
                'semesterNo' => $semesterNo,
                'batch' => $batch,
                'maxSemesters' => $maxSemesters,
                'structures' => $structures,
                'totalBoys' => $totalBoys,
                'totalGirls' => $totalGirls
            ]);
        }

        $batches = ['2026', '2025', '2024', '2023', '2022'];

        $commonHeads = CollegeFeeHead::where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->orderBy('name', 'ASC')
            ->get();

        return view('college_fees.structures', compact(
            'courses',
            'courseId',
            'selectedCourse',
            'semesterNo',
            'maxSemesters',
            'batch',
            'batches',
            'commonHeads',
            'structures',
            'autoOpenStudentId'
        ));
    }

    /**
     * Interactive Student Fee Ledger Matrix Data (AJAX)
     */
public function getLedgerMatrixData(Request $request)
    {
        $branchId = $this->getBranchId();
        $courseId = $request->get('course_id') ? (int)$request->get('course_id') : null;
        $batch = $request->get('batch') ? (string)$request->get('batch') : null;

        if (empty($courseId) || empty($batch)) {
            return response()->json([
                'status' => 'empty',
                'message' => 'Please select a Course and Admission Batch to view fee structures and student records.',
                'kpi' => [
                    'total_enrolled' => 0,
                    'filtered_count' => 0,
                    'net_payable' => 0,
                    'already_paid' => 0,
                    'to_be_assigned' => 0,
                    'assigned_count' => 0,
                    'batch_first_boys' => 0,
                    'batch_first_girls' => 0,
                ],
                'standard_heads' => [],
                'rows' => []
            ]);
        }

        $course = Course::find($courseId);
        $courseName = $course ? $course->name : '';
        $maxSemesters = $course ? ($course->total_semester ?: 6) : 6;

        $semInput = $request->get('semester_no');
        if (empty($semInput) || $semInput === 'auto' || (int)$semInput <= 0) {
            $semesterNo = $this->resolveActiveSemester($branchId, $courseId, $batch, $maxSemesters);
        } else {
            $semesterNo = (int)$semInput;
        }

        $statusFilter = strtolower($request->get('status', 'all'));
        $genderFilter = strtolower($request->get('gender', 'all'));
        $search = trim($request->get('search', ''));

        // 1. Fetch Standard Fee Heads configured for this Course, Semester & Batch
        $standardStructures = CollegeFeeStructure::where('branch_id', $branchId)
            ->where('course_id', $courseId)
            ->where('semester_no', $semesterNo)
            ->where(function($sub) use ($batch) {
                $sub->where('batch', $batch)->orWhereNull('batch');
            })
            ->orderBy('id', 'ASC')
            ->get();

        $stdBoysTotal = (float)$standardStructures->sum('amount_boys');
        $stdGirlsTotal = (float)$standardStructures->sum('amount_girls');

        // 2. Fetch Enrolled Students for this Course & Batch (Optimized)
        $classTypeIds = ClassType::where('course_id', $courseId)->pluck('id')->toArray();

        $studentsQuery = Admission::where('branch_id', $branchId)
            ->where('status', 1)
            ->where('school', 1)
            ->where(function($q) use ($courseName, $classTypeIds) {
                $q->where('course', $courseName);
                if (!empty($classTypeIds)) {
                    $q->orWhereIn('class_type_id', $classTypeIds);
                }
            })
            ->when(!empty($batch), function($q) use ($batch) {
                $q->where('batch', $batch);
            });

        $totalEnrolledInBatch = (clone $studentsQuery)->count();

        // Apply Gender Filter
        if ($genderFilter === 'boys') {
            $studentsQuery->where('gender_id', 1);
        } elseif ($genderFilter === 'girls') {
            $studentsQuery->whereIn('gender_id', [2, 3]);
        }

        // Apply Search Filter
        if (!empty($search)) {
            $studentsQuery->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('father_name', 'LIKE', "%{$search}%")
                  ->orWhere('admissionNo', 'LIKE', "%{$search}%")
                  ->orWhere('userName', 'LIKE', "%{$search}%");
            });
        }

        // Select only necessary fields to avoid hydrating massive Admission models
        $students = $studentsQuery->select('id', 'first_name', 'last_name', 'father_name', 'admissionNo', 'userName', 'gender_id')
            ->orderBy('first_name', 'ASC')
            ->get();
        $studentIds = $students->pluck('id')->toArray();

        // 3. Fetch Fee Assignments (direct DB query with only needed columns, respecting soft deletes)
        $allStudentAssignments = DB::table('college_fee_assignments')
            ->where('branch_id', $branchId)
            ->whereIn('student_id', $studentIds)
            ->where('semester_no', $semesterNo)
            ->whereNull('deleted_at')
            ->select('id', 'student_id', 'head_name', 'gross_amount', 'discount_amount', 'discount_reason', 'net_amount', 'paid_amount', 'due_amount')
            ->get();

        // 4. SQL Aggregation for receipts (replaces hydrating hundreds of receipt models, respecting soft deletes)
        $receiptSums = DB::table('college_fee_receipts')
            ->where('branch_id', $branchId)
            ->whereIn('student_id', $studentIds)
            ->where('semester_no', $semesterNo)
            ->whereNull('deleted_at')
            ->groupBy('student_id')
            ->select('student_id', DB::raw('SUM(amount_paid) as total_paid'))
            ->pluck('total_paid', 'student_id')
            ->toArray();

        // 5. Check which students have progressed to a higher semester
        $progressedMap = DB::table('college_fee_assignments')
            ->where('branch_id', $branchId)
            ->whereIn('student_id', $studentIds)
            ->where('semester_no', '>', $semesterNo)
            ->whereNull('deleted_at')
            ->groupBy('student_id')
            ->select('student_id', DB::raw('MIN(semester_no) as next_semester'))
            ->pluck('next_semester', 'student_id')
            ->toArray();

        // 6. Historic discount reasons fallback (indexed, pluck, respecting soft deletes)
        $otherDiscountReasons = DB::table('college_fee_assignments')
            ->where('branch_id', $branchId)
            ->whereIn('student_id', $studentIds)
            ->whereNull('deleted_at')
            ->whereNotNull('discount_reason')
            ->where('discount_reason', '!=', '')
            ->pluck('discount_reason', 'student_id')
            ->toArray();

        // 7. Build Fee Head Columns: Standard Structures + assigned heads
        $feeHeadMap = [];

        foreach ($standardStructures as $struct) {
            $hName = trim($struct->head_name);
            $normKey = rtrim(strtolower($hName), 's');
            $feeHeadMap[$normKey] = [
                'id' => $struct->id,
                'head_name' => $hName,
                'amount_boys' => (float)$struct->amount_boys,
                'amount_girls' => (float)$struct->amount_girls,
                'is_refundable' => (int)$struct->is_refundable,
                'is_standard' => true
            ];
        }

        // Map student gender for fast lookup during fee head discovery
        $studentGenderMap = [];
        foreach ($students as $stu) {
            $studentGenderMap[$stu->id] = in_array((int)$stu->gender_id, [2, 3]);
        }

        // Process all assignments in a single O(N) array pass
        $stuHeadValues = [];
        $stuTotals = [];

        foreach ($allStudentAssignments as $assign) {
            $sId = $assign->student_id;
            $hName = trim($assign->head_name);
            if (empty($hName)) continue;
            $normKey = rtrim(strtolower($hName), 's');

            if (!isset($feeHeadMap[$normKey])) {
                $feeHeadMap[$normKey] = [
                    'id' => null,
                    'head_name' => ucwords(strtolower($hName)),
                    'amount_boys' => 0,
                    'amount_girls' => 0,
                    'is_refundable' => 0,
                    'is_standard' => false
                ];
            }

            $isGirl = $studentGenderMap[$sId] ?? false;
            $assignGross = (float)$assign->gross_amount;
            if ($assignGross > 0) {
                if ($isGirl) {
                    if ($feeHeadMap[$normKey]['amount_girls'] <= 0) {
                        $feeHeadMap[$normKey]['amount_girls'] = $assignGross;
                    }
                } else {
                    if ($feeHeadMap[$normKey]['amount_boys'] <= 0) {
                        $feeHeadMap[$normKey]['amount_boys'] = $assignGross;
                    }
                }
            }

            $stuHeadValues[$sId][$normKey] = $assign;

            if (!isset($stuTotals[$sId])) {
                $stuTotals[$sId] = [
                    'gross' => 0.0,
                    'discount' => 0.0,
                    'net' => 0.0,
                    'discount_reason' => '',
                    'tuition_gross' => 0.0,
                    'tuition_discount' => 0.0,
                    'tuition_net' => 0.0,
                    'tuition_paid' => 0.0,
                    'has_tuition' => false,
                    'first_gross' => $assignGross,
                    'first_discount' => (float)$assign->discount_amount,
                    'first_net' => (float)$assign->net_amount,
                    'first_paid' => (float)$assign->paid_amount,
                    'count' => 0
                ];
            }

            $grossAmt = (float)$assign->gross_amount;
            $discAmt = (float)$assign->discount_amount;
            $netAmt = (float)$assign->net_amount;

            $stuTotals[$sId]['gross'] += $grossAmt;
            $stuTotals[$sId]['discount'] += $discAmt;
            $stuTotals[$sId]['net'] += $netAmt;
            $stuTotals[$sId]['count']++;

            if (!empty($assign->discount_reason) && empty($stuTotals[$sId]['discount_reason'])) {
                $stuTotals[$sId]['discount_reason'] = $assign->discount_reason;
            }

            if (strpos($normKey, 'tuit') !== false || strpos($normKey, 'tuti') !== false) {
                $stuTotals[$sId]['tuition_gross'] = $grossAmt;
                $stuTotals[$sId]['tuition_discount'] = $discAmt;
                $stuTotals[$sId]['tuition_net'] = $netAmt;
                $stuTotals[$sId]['tuition_paid'] = (float)$assign->paid_amount;
                $stuTotals[$sId]['has_tuition'] = true;
            }
        }

        // Default cross-gender amounts if only one gender amount was found
        foreach ($feeHeadMap as $normKey => &$fHead) {
            if ($fHead['amount_boys'] <= 0 && $fHead['amount_girls'] > 0) {
                $fHead['amount_boys'] = $fHead['amount_girls'];
            } elseif ($fHead['amount_girls'] <= 0 && $fHead['amount_boys'] > 0) {
                $fHead['amount_girls'] = $fHead['amount_boys'];
            }
        }
        unset($fHead);

        // Sort fee head columns: Tuition Fee first
        $feeHeadColumns = [];
        $tuitionKey = null;
        foreach (array_keys($feeHeadMap) as $k) {
            if (strpos($k, 'tuition') !== false || strpos($k, 'tution') !== false) {
                $tuitionKey = $k;
                break;
            }
        }
        if ($tuitionKey && isset($feeHeadMap[$tuitionKey])) {
            $feeHeadColumns[] = $feeHeadMap[$tuitionKey];
            unset($feeHeadMap[$tuitionKey]);
        }
        foreach ($feeHeadMap as $fHead) {
            $feeHeadColumns[] = $fHead;
        }

        // 8. Build Matrix Rows (Fast Array Lookups)
        $matrixRows = [];
        $totalNetPayable = 0;
        $totalAlreadyPaid = 0;
        $toBeAssignedCount = 0;
        $assignedStudentsCount = 0;

        foreach ($students as $stu) {
            $sId = $stu->id;
            $isProgressed = isset($progressedMap[$sId]);
            $nextSemester = $isProgressed ? (int)$progressedMap[$sId] : null;
            $isGirl = $studentGenderMap[$sId] ?? false;
            $genderLabel = $isGirl ? 'Girl' : 'Boy';

            $t = $stuTotals[$sId] ?? null;
            $hasAssignments = ($t !== null && $t['count'] > 0);

            if ($hasAssignments) {
                $assignedStudentsCount++;
            } else {
                $toBeAssignedCount++;
            }

            // Map all Fee Head values column by column (O(1) lookups)
            $headValues = [];
            foreach ($feeHeadColumns as $col) {
                $colHeadName = $col['head_name'];
                $normKey = rtrim(strtolower($colHeadName), 's');
                $assign = $stuHeadValues[$sId][$normKey] ?? null;
                $defAmt = $isGirl ? (float)$col['amount_girls'] : (float)$col['amount_boys'];

                if ($assign) {
                    $headValues[$colHeadName] = [
                        'assigned' => true,
                        'assignment_id' => $assign->id,
                        'amount' => (float)$assign->gross_amount,
                        'paid_amount' => (float)$assign->paid_amount,
                        'due_amount' => (float)$assign->due_amount,
                        'default_amount' => $defAmt
                    ];
                } else {
                    $headValues[$colHeadName] = [
                        'assigned' => false,
                        'assignment_id' => null,
                        'amount' => 0,
                        'paid_amount' => 0,
                        'due_amount' => 0,
                        'default_amount' => $defAmt
                    ];
                }
            }

            $customHeads = [];

            // Totals
            $gross = $t ? (float)$t['gross'] : 0.0;
            $discount = $t ? (float)$t['discount'] : 0.0;
            $discountReason = $t ? $t['discount_reason'] : '';
            if (empty($discountReason)) {
                $discountReason = $otherDiscountReasons[$sId] ?? '';
            }
            $net = $t ? (float)$t['net'] : 0.0;
            $paid = (float)($receiptSums[$sId] ?? 0.0);

            // Tuition Fee specific tracking (or first assign fallback)
            if ($t) {
                if ($t['has_tuition']) {
                    $tuitionGross = (float)$t['tuition_gross'];
                    $tuitionDiscount = (float)$t['tuition_discount'];
                    $tuitionNet = (float)$t['tuition_net'];
                    $tuitionPaid = (float)$t['tuition_paid'];
                } else {
                    $tuitionGross = (float)$t['first_gross'];
                    $tuitionDiscount = (float)$t['first_discount'];
                    $tuitionNet = (float)$t['first_net'];
                    $tuitionPaid = (float)$t['first_paid'];
                }
            } else {
                $tuitionGross = 0.0;
                $tuitionDiscount = 0.0;
                $tuitionNet = 0.0;
                $tuitionPaid = 0.0;
            }

            // Status & Outstanding Determination
            $transferredDues = 0;
            if ($isProgressed) {
                $transferredDues = round($net - $paid, 2);
                $outstanding = 0;
                $status = 'Progressed';
            } else {
                $outstanding = round($net - $paid, 2);
                if (!$hasAssignments) {
                    $status = 'Unassigned';
                } elseif ($outstanding <= 0) {
                    $status = 'Paid';
                } elseif ($paid > 0) {
                    $status = 'Partial';
                } else {
                    $status = 'Pending';
                }
            }

            // Apply Status Filter
            if ($statusFilter !== 'all') {
                if ($statusFilter === 'pending' && $status !== 'Pending') continue;
                if ($statusFilter === 'partial' && $status !== 'Partial') continue;
                if ($statusFilter === 'paid' && $status !== 'Paid') continue;
            }

            $totalNetPayable += $net;
            $totalAlreadyPaid += $paid;

            $matrixRows[] = [
                'student_id' => $sId,
                'name' => trim(($stu->first_name ?: '') . ' ' . ($stu->last_name ?: '')) ?: ($stu->userName ?: 'Student'),
                'father_name' => trim($stu->father_name ?: ''),
                'roll_no' => $stu->admissionNo ?: ($stu->userName ?: ('BC' . substr($batch, -2) . '-' . str_pad($sId, 3, '0', STR_PAD_LEFT))),
                'gender' => $genderLabel,
                'is_girl' => $isGirl,
                'head_values' => $headValues,
                'custom_heads' => $customHeads,
                'tuition_gross' => $tuitionGross,
                'tuition_discount' => $tuitionDiscount,
                'tuition_net' => $tuitionNet,
                'tuition_paid' => $tuitionPaid,
                'gross' => $gross,
                'discount' => $discount,
                'discount_reason' => $discountReason,
                'net' => $net,
                'paid' => $paid,
                'outstanding' => $outstanding,
                'status' => $status,
                'is_progressed' => $isProgressed,
                'next_semester' => $nextSemester,
                'transferred_dues' => $transferredDues
            ];
        }

        return response()->json([
            'status' => 'success',
            'active_semester' => $semesterNo,
            'max_semesters' => $maxSemesters,
            'course' => [
                'id' => $course->id ?? 1,
                'name' => $course->name ?? 'Course',
                'max_semesters' => $maxSemesters
            ],
            'batch' => $batch,
            'semester_no' => $semesterNo,
            'standard_heads' => $feeHeadColumns,
            'fee_heads' => $feeHeadColumns,
            'kpi' => [
                'total_enrolled' => $totalEnrolledInBatch,
                'filtered_count' => count($matrixRows),
                'net_payable' => $totalNetPayable,
                'already_paid' => $totalAlreadyPaid,
                'to_be_assigned' => $toBeAssignedCount,
                'assigned_count' => $assignedStudentsCount,
                'batch_first_boys' => $stdBoysTotal,
                'batch_first_girls' => $stdGirlsTotal
            ],
            'rows' => $matrixRows
        ]);
    }

    /**
     * Real-time cell edit for a standard fee head
     */
    public function updateCellFee(Request $request)
    {
        $branchId = $this->getBranchId();
        $studentId = (int)$request->student_id;
        $courseId = (int)$request->course_id;
        $semesterNo = (int)$request->semester_no;

        // Progressed semester is locked
        if (CollegeFeeAssignment::where('branch_id', $branchId)->where('student_id', $studentId)->where('semester_no', '>', $semesterNo)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Cannot modify fees for a completed/progressed semester.'], 422);
        }
        $headName = trim($request->head_name);
        $isPreviousDues = (stripos($headName, 'previous dues') !== false);
        $rawAmount = (float)$request->amount;
        $newAmount = $isPreviousDues ? $rawAmount : max(0, $rawAmount);

        DB::beginTransaction();
        try {
            $assign = CollegeFeeAssignment::where('branch_id', $branchId)
                ->where('student_id', $studentId)
                ->where('semester_no', $semesterNo)
                ->where(function($q) use ($headName) {
                    $q->where('head_name', $headName)
                      ->orWhereRaw('LOWER(TRIM(head_name)) = ?', [strtolower(trim($headName))]);
                })
                ->first();

            if ($assign) {
                // Rule: If payment has already been collected on this head, it is LOCKED from modification
                if ($assign->paid_amount > 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Fee head '{$headName}' has collected payment (₹{$assign->paid_amount}). It cannot be modified directly."
                    ], 422);
                }

                if ($newAmount == 0 && !$isPreviousDues) {
                    $assign->delete();
                } else {
                    if (!$assign->structure_id) {
                        $matchingStruct = CollegeFeeStructure::where('branch_id', $branchId)
                            ->where('course_id', $courseId)
                            ->where('semester_no', $semesterNo)
                            ->where(function($q) use ($headName) {
                                $q->where('head_name', $headName)
                                  ->orWhereRaw('LOWER(TRIM(head_name)) = ?', [strtolower(trim($headName))]);
                            })
                            ->first();
                        if ($matchingStruct) {
                            $assign->structure_id = $matchingStruct->id;
                        }
                    }
                    $assign->gross_amount = $newAmount;
                    $assign->net_amount = max(0, $newAmount - $assign->discount_amount);
                    $assign->due_amount = max(0, $assign->net_amount - $assign->paid_amount);
                    $assign->status = ($assign->due_amount <= 0) ? 'Paid' : ($assign->paid_amount > 0 ? 'Partial' : 'Unpaid');
                    $assign->save();
                }
            } elseif ($isPreviousDues) {
                // User requirement: Previous Dues cannot be manually created or added
                return response()->json([
                    'status' => 'error',
                    'message' => 'Previous Dues is an automatic progression rollover head and cannot be manually created.'
                ], 422);
            } elseif ($newAmount != 0) {
                $matchingStruct = CollegeFeeStructure::where('branch_id', $branchId)
                    ->where('course_id', $courseId)
                    ->where('semester_no', $semesterNo)
                    ->where(function($q) use ($headName) {
                        $q->where('head_name', $headName)
                          ->orWhereRaw('LOWER(TRIM(head_name)) = ?', [strtolower(trim($headName))]);
                    })
                    ->first();

                $assign = new CollegeFeeAssignment();
                $assign->branch_id = $branchId;
                $assign->student_id = $studentId;
                $assign->course_id = $courseId;
                $assign->semester_no = $semesterNo;
                $assign->structure_id = $matchingStruct ? $matchingStruct->id : null;
                $assign->head_name = $headName;
                $assign->gross_amount = $newAmount;
                $assign->discount_amount = 0;
                $assign->net_amount = $newAmount;
                $assign->paid_amount = 0;
                $assign->due_amount = $newAmount;
                $assign->is_carried_dues = $isPreviousDues ? 1 : 0;
                $assign->status = ($newAmount <= 0) ? 'Paid' : 'Unpaid';
                $assign->created_by = Session::get('id') ?: 1;
                $assign->save();
            }

            DB::commit();
            return $this->getStudentRecalculatedRow($studentId, $semesterNo, [
                'head_name' => $headName,
                'new_amount' => $newAmount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Database update error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update discount and reason for a student
     */

        /**
     * 2b. Add Fee Head directly to Student
     */
    public function addHeadToStudent(Request $request)
    {
        $branchId = $this->getBranchId();
        $studentId = (int)$request->student_id;
        $courseId = (int)$request->course_id;
        $semesterNo = (int)$request->semester_no;
        $headName = trim($request->head_name);
        $amount = (float)$request->amount;

        // Progressed semester is locked
        if (CollegeFeeAssignment::where('branch_id', $branchId)->where('student_id', $studentId)->where('semester_no', '>', $semesterNo)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Cannot add fee head to a completed/progressed semester.'], 422);
        }

        if (empty($headName) || $amount <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Please provide a valid head name and amount.'], 422);
        }

        if ($this->isCoreSystemHeadName($headName)) {
            return response()->json([
                'status' => 'error',
                'message' => "Fee Head '{$headName}' cannot be manually added. Tuition Fee is standard and Previous Dues is managed by Semester Progression."
            ], 422);
        }

        $existing = CollegeFeeAssignment::where('branch_id', $branchId)
            ->where('student_id', $studentId)
            ->where('semester_no', $semesterNo)
            ->where('head_name', $headName)
            ->first();

        if ($existing) {
            return response()->json(['status' => 'error', 'message' => "Head '{$headName}' already exists for this student in Semester {$semesterNo}."], 422);
        }

        DB::beginTransaction();
        try {
            $matchingStruct = CollegeFeeStructure::where('branch_id', $branchId)
                ->where('course_id', $courseId)
                ->where('semester_no', $semesterNo)
                ->where('head_name', $headName)
                ->first();

            $assign = new CollegeFeeAssignment();
            $assign->branch_id = $branchId;
            $assign->student_id = $studentId;
            $assign->course_id = $courseId;
            $assign->semester_no = $semesterNo;
            $assign->structure_id = $matchingStruct ? $matchingStruct->id : null;
            $assign->head_name = $headName;
            $assign->gross_amount = $amount;
            $assign->discount_amount = 0;
            $assign->net_amount = $amount;
            $assign->paid_amount = 0;
            $assign->due_amount = $amount;
            $assign->status = $amount > 0 ? 'Unpaid' : 'Paid';
            $assign->created_by = Session::get('id') ?: 1;
            $assign->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Head '{$headName}' (₹" . number_format($amount, 2) . ") added successfully to student.",
                'assignment_id' => $assign->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Failed to add fee head: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 2c. Remove Student-Specific Fee Head
     */
    public function removeStudentHead(Request $request)
    {
        $branchId = $this->getBranchId();
        $assignmentId = (int)$request->assignment_id;

        $assign = CollegeFeeAssignment::where('branch_id', $branchId)->findOrFail($assignmentId);
        $studentId = $assign->student_id;
        $semesterNo = $assign->semester_no;

        // Progressed semester is locked
        if (CollegeFeeAssignment::where('branch_id', $branchId)->where('student_id', $studentId)->where('semester_no', '>', $semesterNo)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Cannot remove fee head from a completed/progressed semester.'], 422);
        }

        // Core system heads (Tuition Fees & Previous Dues) cannot be deleted from student
        if ($this->isCoreSystemHeadName($assign->head_name) || !empty($assign->is_carried_dues)) {
            return response()->json([
                'status' => 'error',
                'message' => "Fee Head '{$assign->head_name}' is a system core fee head and cannot be removed."
            ], 422);
        }

        if ($assign->paid_amount > 0) {
            if (!$request->boolean('force')) {
                return response()->json([
                    'status' => 'error',
                    'has_payments' => true,
                    'paid_amount' => (float)$assign->paid_amount,
                    'message' => 'Payment has already been collected on this fee head (₹' . number_format($assign->paid_amount, 2) . '). Confirm to remove fee head and reverse its payment records.'
                ], 422);
            }

            // Remove associated receipt items and adjust receipts
            $receiptItems = CollegeFeeReceiptItem::where('assignment_id', $assign->id)->get();
            foreach ($receiptItems as $rItem) {
                $receipt = CollegeFeeReceipt::find($rItem->receipt_id);
                if ($receipt) {
                    $receipt->amount_paid = max(0, $receipt->amount_paid - $rItem->amount_allocated);
                    if ($receipt->amount_paid <= 0) {
                        $receipt->delete();
                    } else {
                        $receipt->save();
                    }
                }
                $rItem->delete();
            }
        }

        $assign->delete();

        // Recalculate totals
        $remaining = CollegeFeeAssignment::where('branch_id', $branchId)
            ->where('student_id', $studentId)
            ->where('semester_no', $semesterNo)
            ->get();

        $gross = (float)$remaining->sum('gross_amount');
        $discount = (float)$remaining->sum('discount_amount');
        $net = (float)$remaining->sum('net_amount');
        $paid = (float)$remaining->sum('paid_amount');
        $due = $net - $paid;

        return response()->json([
            'status' => 'success',
            'message' => 'Fee head removed successfully!',
            'student_id' => $studentId,
            'totals' => [
                'gross' => $gross,
                'discount' => $discount,
                'net' => $net,
                'paid' => $paid,
                'due' => $due
            ]
        ]);
    }

public function updateDiscount(Request $request)
    {
        $branchId = $this->getBranchId();
        $studentId = (int)$request->student_id;
        $semesterNo = (int)$request->semester_no;
        $discountReason = trim($request->get('discount_reason', ''));

        // Progressed semester is locked
        if (CollegeFeeAssignment::where('branch_id', $branchId)->where('student_id', $studentId)->where('semester_no', '>', $semesterNo)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Cannot modify discount for a completed/progressed semester.'], 422);
        }

        $assigns = CollegeFeeAssignment::where('branch_id', $branchId)
            ->where('student_id', $studentId)
            ->where('semester_no', $semesterNo)
            ->get();

        if ($assigns->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Fee heads must be assigned to the student before applying a discount.'], 422);
        }

        // Strictly locate Tuition Fee head
        $tuitionAssign = $assigns->first(function($a) {
            $name = strtolower($a->head_name);
            return strpos($name, 'tuit') !== false || strpos($name, 'tuti') !== false;
        }) ?: $assigns->first();

        if (!$tuitionAssign) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tuition fee head not found for this student.'
            ], 422);
        }

        $tuitionGross = (float)$tuitionAssign->gross_amount;
        $tuitionPaid = (float)$tuitionAssign->paid_amount;

        $discountAmount = $request->has('discount_amount')
            ? max(0, (float)$request->discount_amount)
            : (float)$tuitionAssign->discount_amount;

        // Rule 1: Discount cannot exceed the Tuition Fee Gross Amount
        if ($discountAmount > $tuitionGross) {
            return response()->json([
                'status' => 'error',
                'message' => 'Discount cannot exceed the Tuition Fee amount of ₹' . number_format($tuitionGross, 2) . '.'
            ], 422);
        }

        // Rule 2: Net tuition cannot be less than already collected tuition payment
        $maxAllowedDiscount = max(0, $tuitionGross - $tuitionPaid);
        if ($discountAmount > $maxAllowedDiscount) {
            return response()->json([
                'status' => 'error',
                'message' => 'Discount cannot reduce Net Tuition below already collected tuition payments (₹' . number_format($tuitionPaid, 2) . '). Maximum allowable tuition discount is ₹' . number_format($maxAllowedDiscount, 2) . '.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Apply discount strictly to tuition head
            $tuitionAssign->discount_amount = $discountAmount;
            $tuitionAssign->discount_reason = $discountAmount > 0 ? $discountReason : null;
            $tuitionAssign->net_amount = max(0, $tuitionAssign->gross_amount - $discountAmount);
            $tuitionAssign->due_amount = max(0, $tuitionAssign->net_amount - $tuitionAssign->paid_amount);
            $tuitionAssign->status = $tuitionAssign->due_amount <= 0 ? 'Paid' : ($tuitionAssign->paid_amount > 0 ? 'Partial' : 'Unpaid');
            $tuitionAssign->save();

            // Ensure all other non-tuition heads have 0 discount
            foreach ($assigns as $other) {
                if ($other->id !== $tuitionAssign->id && $other->discount_amount > 0) {
                    $other->discount_amount = 0;
                    $other->discount_reason = null;
                    $other->net_amount = $other->gross_amount;
                    $other->due_amount = max(0, $other->net_amount - $other->paid_amount);
                    $other->status = $other->due_amount <= 0 ? 'Paid' : ($other->paid_amount > 0 ? 'Partial' : 'Unpaid');
                    $other->save();
                }
            }

            DB::commit();

            return $this->getStudentRecalculatedRow($studentId, $semesterNo);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Apply bulk tuition discount across filtered or selected students
     */
    public function applyBulkDiscount(Request $request)
    {
        $branchId = $this->getBranchId();
        $studentIds = (array)$request->student_ids;
        $semesterNo = (int)$request->semester_no;
        $discountType = $request->get('discount_type', 'fixed');
        $discountValue = (float)$request->discount_value;
        $discountReason = trim($request->get('discount_reason', 'Bulk Tuition Discount'));

        if (empty($studentIds) && $request->has('course_id')) {
            $courseId = (int)$request->course_id;
            $batch = $request->batch;
            $genderFilter = $request->get('gender_filter', 'all');

            $course = Course::find($courseId);
            $cName = $course->name ?? '';

            $studentIds = Admission::where('branch_id', $branchId)
                ->where('status', 1)
                ->where('school', 1)
                ->where(function($q) use ($courseId, $cName) {
                    $q->where('course', $cName)
                      ->orWhereHas('ClassTypes', function($sub) use ($courseId) {
                          $sub->where('course_id', $courseId);
                      });
                })
                ->when(!empty($batch), function($q) use ($batch) {
                    $q->where('batch', $batch);
                })
                ->when($genderFilter === 'boys', function($q) {
                    $q->whereNotIn('gender_id', [2, 3]);
                })
                ->when($genderFilter === 'girls', function($q) {
                    $q->whereIn('gender_id', [2, 3]);
                })
                ->pluck('id')
                ->toArray();
        }

        if (empty($studentIds) || $discountValue <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Please select students and enter valid discount value.'], 422);
        }

        $appliedCount = 0;
        foreach ($studentIds as $stuId) {
            $assigns = CollegeFeeAssignment::where('branch_id', $branchId)
                ->where('student_id', $stuId)
                ->where('semester_no', $semesterNo)
                ->get();

            if ($assigns->isEmpty()) continue;

            $tuitionAssign = $assigns->first(function($a) {
                $name = strtolower($a->head_name);
                return strpos($name, 'tuit') !== false || strpos($name, 'tuti') !== false;
            }) ?: $assigns->first();

            if (!$tuitionAssign) continue;

            $tuitionGross = (float)$tuitionAssign->gross_amount;
            $discAmt = $discountType === 'percent' ? ($tuitionGross * $discountValue / 100) : $discountValue;
            $discAmt = min($tuitionGross, $discAmt);

            $req = new Request([
                'student_id' => $stuId,
                'semester_no' => $semesterNo,
                'discount_amount' => $discAmt,
                'discount_reason' => $discountReason
            ]);
            $this->updateDiscount($req);
            $appliedCount++;
        }

        return response()->json([
            'status' => 'success',
            'message' => "Bulk tuition discount successfully applied to {$appliedCount} students!"
        ]);
    }

    /**
     * Save / Edit standard batch fee heads & 1-click apply
     */
    public function saveBatchStandardFees(Request $request)
    {
        $branchId = $this->getBranchId();
        $courseId = (int)$request->course_id;
        $batch = (string)$request->batch;
        $semesterNo = (int)$request->semester_no;
        $heads = (array)$request->heads;
        $autoApply = $request->has('auto_apply') && $request->auto_apply == 1;

        DB::beginTransaction();
        try {
            $course = Course::find($courseId);
            $courseName = $course ? $course->name : '';

            $updatedHeadNames = [];
            foreach ($heads as $h) {
                $headName = trim($h['head_name'] ?? '');
                if (empty($headName)) continue;
                $updatedHeadNames[] = $headName;

                $struct = CollegeFeeStructure::where('branch_id', $branchId)
                    ->where('course_id', $courseId)
                    ->where('semester_no', $semesterNo)
                    ->where(function($q) use ($batch) {
                        $q->where('batch', $batch)->orWhereNull('batch');
                    })
                    ->where('head_name', $headName)
                    ->first();

                if (!$struct) {
                    $struct = new CollegeFeeStructure();
                    $struct->branch_id = $branchId;
                    $struct->course_id = $courseId;
                    $struct->semester_no = $semesterNo;
                    $struct->batch = $batch;
                    $struct->head_name = $headName;
                }

                $struct->amount_boys = max(0, (float)($h['amount_boys'] ?? 0));
                $struct->amount_girls = max(0, (float)($h['amount_girls'] ?? 0));
                $struct->is_refundable = !empty($h['is_refundable']) ? 1 : 0;
                $struct->created_by = Session::get('id') ?: 1;
                $struct->save();
            }

            $assignedCount = 0;
            if ($autoApply) {
                $students = Admission::where('branch_id', $branchId)
                    ->where('status', 1)
                    ->where('school', 1)
                    ->where(function($q) use ($courseId, $courseName) {
                        $q->where('course', $courseName)
                          ->orWhereHas('ClassTypes', function($sub) use ($courseId) {
                              $sub->where('course_id', $courseId);
                          });
                    })
                    ->where('batch', $batch)
                    ->get();

                $studentIds = $students->pluck('id')->toArray();

                $savedStructures = CollegeFeeStructure::where('branch_id', $branchId)
                    ->where('course_id', $courseId)
                    ->where('semester_no', $semesterNo)
                    ->whereIn('head_name', $updatedHeadNames)
                    ->get();

                // Prefetch existing assignments in a single query
                $existingMap = [];
                if (!empty($studentIds)) {
                    $existingAssignments = CollegeFeeAssignment::where('branch_id', $branchId)
                        ->whereIn('student_id', $studentIds)
                        ->where('semester_no', $semesterNo)
                        ->get(['student_id', 'head_name']);

                    foreach ($existingAssignments as $ea) {
                        $existingMap[$ea->student_id . '_' . $ea->head_name] = true;
                    }
                }

                $inserts = [];
                $userId = Session::get('id') ?: 1;
                $now = now();

                foreach ($students as $stu) {
                    $isGirl = in_array((int)$stu->gender_id, [2, 3]);

                    foreach ($savedStructures as $st) {
                        $amt = $isGirl ? (float)$st->amount_girls : (float)$st->amount_boys;
                        if ($amt <= 0 && $st->amount_boys > 0) $amt = (float)$st->amount_boys;

                        $key = $stu->id . '_' . $st->head_name;
                        if (!isset($existingMap[$key]) && $amt > 0) {
                            $inserts[] = [
                                'branch_id' => $branchId,
                                'student_id' => $stu->id,
                                'course_id' => $courseId,
                                'semester_no' => $semesterNo,
                                'structure_id' => $st->id,
                                'head_name' => $st->head_name,
                                'gross_amount' => $amt,
                                'discount_amount' => 0,
                                'discount_reason' => null,
                                'net_amount' => $amt,
                                'paid_amount' => 0,
                                'due_amount' => $amt,
                                'status' => 'Unpaid',
                                'created_by' => $userId,
                                'created_at' => $now,
                                'updated_at' => $now
                            ];
                            $existingMap[$key] = true;
                            $assignedCount++;
                        }
                    }
                }

                if (!empty($inserts)) {
                    foreach (array_chunk($inserts, 250) as $chunk) {
                        CollegeFeeAssignment::insert($chunk);
                    }
                }
            }

            DB::commit();

            $msg = 'Batch standard fee heads updated successfully!';
            if ($assignedCount > 0) {
                $msg .= " & auto-assigned to {$assignedCount} student records!";
            }

            return response()->json([
                'status' => 'success',
                'message' => $msg,
                'assigned_count' => $assignedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper to return recalculated row data for a student
     */
    protected function getStudentRecalculatedRow($studentId, $semesterNo, $extra = [])
    {
        $branchId = $this->getBranchId();

        $assigns = CollegeFeeAssignment::where('branch_id', $branchId)
            ->where('student_id', $studentId)
            ->where('semester_no', $semesterNo)
            ->get();

        $paid = (float)CollegeFeeReceipt::where('branch_id', $branchId)
            ->where('student_id', $studentId)
            ->where('semester_no', $semesterNo)
            ->sum('amount_paid');

        $gross = (float)$assigns->sum('gross_amount');
        $discount = (float)$assigns->sum('discount_amount');
        $discountReason = $assigns->whereNotNull('discount_reason')->pluck('discount_reason')->first() ?: '';
        $net = (float)$assigns->sum('net_amount');

        $tuitionAssign = $assigns->first(function($a) {
            $name = strtolower($a->head_name);
            return strpos($name, 'tuit') !== false || strpos($name, 'tuti') !== false;
        }) ?: $assigns->first();

        $tuitionGross = $tuitionAssign ? (float)$tuitionAssign->gross_amount : 0;
        $tuitionDiscount = $tuitionAssign ? (float)$tuitionAssign->discount_amount : 0;
        $tuitionNet = $tuitionAssign ? (float)$tuitionAssign->net_amount : 0;
        $tuitionPaid = $tuitionAssign ? (float)$tuitionAssign->paid_amount : 0;

        $isProgressed = CollegeFeeAssignment::where('branch_id', $branchId)
            ->where('student_id', $studentId)
            ->where('semester_no', '>', $semesterNo)
            ->exists();
        $nextSemester = null;
        $transferredDues = 0;
        if ($isProgressed) {
            $nextSemester = CollegeFeeAssignment::where('branch_id', $branchId)
                ->where('student_id', $studentId)
                ->where('semester_no', '>', $semesterNo)
                ->min('semester_no');
            $transferredDues = round($net - $paid, 2);
            $outstanding = 0;
            $status = 'Progressed';
        } else {
            $outstanding = round($net - $paid, 2);
            if ($assigns->isEmpty()) {
                $status = 'Unassigned';
            } elseif ($outstanding <= 0) {
                $status = 'Paid';
            } elseif ($paid > 0) {
                $status = 'Partial';
            } else {
                $status = 'Pending';
            }
        }

        $resp = [
            'status' => 'success',
            'message' => 'Updated successfully!',
            'row' => [
                'student_id' => $studentId,
                'tuition_gross' => $tuitionGross,
                'tuition_discount' => $tuitionDiscount,
                'tuition_net' => $tuitionNet,
                'tuition_paid' => $tuitionPaid,
                'gross' => $gross,
                'discount' => $discount,
                'discount_reason' => $discountReason,
                'net' => $net,
                'paid' => $paid,
                'outstanding' => $outstanding,
                'status' => $status,
                'is_progressed' => $isProgressed,
                'next_semester' => $nextSemester,
                'transferred_dues' => $transferredDues
            ]
        ];
        if (!empty($extra)) {
            $resp = array_merge($resp, $extra);
        }
        return response()->json($resp);
    }

    /**
     * Save/Update Fee Structure Head
     */
        /**
     * 2d. Assign Fee Heads to Selected Students in Bulk (High Performance Bulk Optimized)
     */
    public function assignHeadsToSelectedStudents(Request $request)
    {
        $branchId = $this->getBranchId();
        $courseId = (int)$request->course_id;
        $batch = (string)$request->batch;
        $semesterNo = (int)$request->semester_no;
        $studentIds = (array)$request->student_ids;
        $heads = (array)$request->heads;
        $assignMode = (string)$request->get('assign_mode', 'unassigned'); // 'unassigned' or 'overwrite'

        if (empty($studentIds)) {
            return response()->json(['status' => 'error', 'message' => 'Please select at least one student.'], 422);
        }
        if (empty($heads)) {
            return response()->json(['status' => 'error', 'message' => 'Please select at least one fee head to assign.'], 422);
        }

        $students = Admission::where('branch_id', $branchId)
            ->whereIn('id', $studentIds)
            ->get(['id', 'gender_id']);

        $stdStructures = CollegeFeeStructure::where('branch_id', $branchId)
            ->where('course_id', $courseId)
            ->where('semester_no', $semesterNo)
            ->get();

        DB::beginTransaction();
        try {
            // Bulk Prefetch all existing assignments for these students in this semester (1 query!)
            $existingAssignments = CollegeFeeAssignment::where('branch_id', $branchId)
                ->whereIn('student_id', $studentIds)
                ->where('semester_no', $semesterNo)
                ->get(['id', 'student_id', 'head_name', 'structure_id', 'gross_amount', 'discount_amount', 'paid_amount', 'due_amount', 'status']);

            $existingMap = [];
            foreach ($existingAssignments as $ea) {
                $key = $ea->student_id . '_' . strtolower(trim($ea->head_name));
                $existingMap[$key] = $ea;
            }

            $createdCount = 0;
            $overwrittenCount = 0;
            $skippedCount = 0;
            $userId = Session::get('id') ?: 1;
            $now = now();
            $inserts = [];

            foreach ($heads as $h) {
                $headName = trim($h['head_name'] ?? $h['name'] ?? '');
                if (empty($headName)) continue;
                $normHead = strtolower(trim($headName));

                // Never allow manual assignment of Previous Dues or Tuition Fees in Quick Assign
                if (strpos($normHead, 'previous dues') !== false || strpos($normHead, 'prev dues') !== false || strpos($normHead, 'previous fee') !== false || strpos($normHead, 'tuition') !== false || strpos($normHead, 'tution') !== false) {
                    continue;
                }

                $boysAmt = isset($h['amount_boys']) && $h['amount_boys'] !== '' ? (float)$h['amount_boys'] : (isset($h['amount']) ? (float)$h['amount'] : 0);
                $girlsAmt = isset($h['amount_girls']) && $h['amount_girls'] !== '' ? (float)$h['amount_girls'] : (isset($h['amount']) ? (float)$h['amount'] : 0);

                $struct = $stdStructures->first(function($s) use ($headName) {
                    return strtolower(trim($s->head_name)) === strtolower(trim($headName));
                });

                if ($boysAmt <= 0 && $struct) {
                    $boysAmt = (float)$struct->amount_boys;
                }
                if ($girlsAmt <= 0 && $struct) {
                    $girlsAmt = (float)$struct->amount_girls > 0 ? (float)$struct->amount_girls : (float)$struct->amount_boys;
                }

                $boyUpdateIds = [];
                $girlUpdateIds = [];

                foreach ($students as $stu) {
                    $isGirl = in_array((int)$stu->gender_id, [2, 3]);
                    $key = $stu->id . '_' . $normHead;
                    $existing = $existingMap[$key] ?? null;

                    if ($existing) {
                        if ($assignMode === 'overwrite') {
                            if ($isGirl) {
                                $girlUpdateIds[] = $existing->id;
                            } else {
                                $boyUpdateIds[] = $existing->id;
                            }
                            $overwrittenCount++;
                        } else {
                            $skippedCount++;
                        }
                    } else {
                        $amt = $isGirl ? $girlsAmt : $boysAmt;
                        $inserts[] = [
                            'branch_id' => $branchId,
                            'student_id' => $stu->id,
                            'course_id' => $courseId,
                            'semester_no' => $semesterNo,
                            'structure_id' => $struct ? $struct->id : null,
                            'head_name' => $headName,
                            'gross_amount' => $amt,
                            'discount_amount' => 0,
                            'discount_reason' => null,
                            'net_amount' => $amt,
                            'paid_amount' => 0,
                            'due_amount' => $amt,
                            'status' => $amt > 0 ? 'Unpaid' : 'Paid',
                            'created_by' => $userId,
                            'created_at' => $now,
                            'updated_at' => $now
                        ];
                        $existingMap[$key] = true;
                        $createdCount++;
                    }
                }

                // High-performance batch updates for boys
                if (!empty($boyUpdateIds)) {
                    foreach (array_chunk($boyUpdateIds, 500) as $chunkIds) {
                        $idList = implode(',', array_map('intval', $chunkIds));
                        DB::statement("
                            UPDATE college_fee_assignments
                            SET 
                                head_name = :head_name,
                                structure_id = COALESCE(structure_id, :struct_id),
                                gross_amount = :amt,
                                net_amount = GREATEST(0, :amt - discount_amount),
                                due_amount = GREATEST(0, GREATEST(0, :amt - discount_amount) - paid_amount),
                                status = CASE 
                                    WHEN GREATEST(0, GREATEST(0, :amt - discount_amount) - paid_amount) <= 0 THEN 'Paid'
                                    WHEN paid_amount > 0 THEN 'Partial'
                                    ELSE 'Unpaid'
                                END,
                                updated_at = NOW()
                            WHERE id IN ({$idList})
                        ", [
                            'head_name' => $headName,
                            'struct_id' => $struct ? $struct->id : null,
                            'amt' => $boysAmt
                        ]);
                    }
                }

                // High-performance batch updates for girls
                if (!empty($girlUpdateIds)) {
                    foreach (array_chunk($girlUpdateIds, 500) as $chunkIds) {
                        $idList = implode(',', array_map('intval', $chunkIds));
                        DB::statement("
                            UPDATE college_fee_assignments
                            SET 
                                head_name = :head_name,
                                structure_id = COALESCE(structure_id, :struct_id),
                                gross_amount = :amt,
                                net_amount = GREATEST(0, :amt - discount_amount),
                                due_amount = GREATEST(0, GREATEST(0, :amt - discount_amount) - paid_amount),
                                status = CASE 
                                    WHEN GREATEST(0, GREATEST(0, :amt - discount_amount) - paid_amount) <= 0 THEN 'Paid'
                                    WHEN paid_amount > 0 THEN 'Partial'
                                    ELSE 'Unpaid'
                                END,
                                updated_at = NOW()
                            WHERE id IN ({$idList})
                        ", [
                            'head_name' => $headName,
                            'struct_id' => $struct ? $struct->id : null,
                            'amt' => $girlsAmt
                        ]);
                    }
                }
            }

            // High-performance bulk insert in chunks of 250
            if (!empty($inserts)) {
                foreach (array_chunk($inserts, 250) as $chunk) {
                    CollegeFeeAssignment::insert($chunk);
                }
            }

            DB::commit();

            if ($assignMode === 'overwrite') {
                $msg = "Fee heads processed across " . count($students) . " students: {$createdCount} newly assigned, {$overwrittenCount} overwritten/updated.";
            } else {
                $msg = "Fee heads assigned across " . count($students) . " students: {$createdCount} newly assigned" . ($skippedCount > 0 ? ", {$skippedCount} skipped (already assigned)." : ".");
            }

            return response()->json([
                'status' => 'success',
                'message' => $msg,
                'created_count' => $createdCount,
                'overwritten_count' => $overwrittenCount,
                'skipped_count' => $skippedCount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Failed bulk assigning fee heads: ' . $e->getMessage()], 500);
        }
    }

public function saveStructure(Request $request)
    {
        $branchId = $this->getBranchId();

        $request->validate([
            'course_id' => 'required|integer',
            'semester_no' => 'required|integer',
            'head_name' => 'required|string|max:100',
            'amount_boys' => 'required|numeric|min:0',
            'amount_girls' => 'required|numeric|min:0',
        ]);

        $id = $request->get('structure_id');
        $structure = $id 
            ? CollegeFeeStructure::where('branch_id', $branchId)->findOrFail($id) 
            : new CollegeFeeStructure();

        $structure->branch_id = $branchId;
        $structure->course_id = $request->course_id;
        $structure->semester_no = $request->semester_no;
        $structure->batch = $request->batch ?: date('Y');
        $structure->head_name = trim($request->head_name);
        $structure->amount_boys = $request->amount_boys;
        $structure->amount_girls = $request->amount_girls;
        $structure->due_date = $request->due_date;
        $structure->created_by = Session::get('id') ?: 1;
        $structure->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Fee structure head saved successfully!',
                'data' => $structure
            ]);
        }

        return redirect()->back()->with('message', 'Fee structure head saved successfully!');
    }

    /**
     * Delete Fee Structure Head
     */
    public function deleteStructure($id)
    {
        $branchId = $this->getBranchId();
        $structure = CollegeFeeStructure::where('branch_id', $branchId)->findOrFail($id);
        if ($this->isCoreSystemHeadName($structure->head_name)) {
            $msg = "Fee structure '{$structure->head_name}' is a system core fee head and cannot be deleted!";
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }
        $structure->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Fee structure head deleted successfully!'
            ]);
        }

        return redirect()->back()->with('message', 'Fee structure head deleted successfully!');
    }

    /**
     * Copy Fee Structure from one semester/batch to another
     */
    public function copyStructure(Request $request)
    {
        $branchId = $this->getBranchId();

        $request->validate([
            'from_course_id' => 'required|integer',
            'from_semester' => 'required|integer',
            'from_batch' => 'nullable|string',
            'to_course_id' => 'required|integer',
            'to_semester' => 'required|integer',
            'to_batch' => 'required|string',
        ]);

        $sourceStructures = CollegeFeeStructure::where('branch_id', $branchId)
            ->where('course_id', $request->from_course_id)
            ->where('semester_no', $request->from_semester)
            ->when(!empty($request->from_batch), function($q) use ($request) {
                $q->where('batch', $request->from_batch);
            })
            ->get();

        if ($sourceStructures->isEmpty()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No fee heads found in source structure to copy.'
                ], 422);
            }
            return redirect()->back()->with('error', 'No fee heads found in source structure to copy.');
        }

        $copiedCount = 0;
        foreach ($sourceStructures as $src) {
            $existing = CollegeFeeStructure::where('branch_id', $branchId)
                ->where('course_id', $request->to_course_id)
                ->where('semester_no', $request->to_semester)
                ->where('batch', $request->to_batch)
                ->where('head_name', $src->head_name)
                ->first();

            if (!$existing) {
                $newHead = new CollegeFeeStructure();
                $newHead->branch_id = $branchId;
                $newHead->course_id = $request->to_course_id;
                $newHead->semester_no = $request->to_semester;
                $newHead->batch = $request->to_batch;
                $newHead->head_name = $src->head_name;
                $newHead->amount_boys = $src->amount_boys;
                $newHead->amount_girls = $src->amount_girls;
                $newHead->due_date = $src->due_date;
                $newHead->created_by = Session::get('id') ?: 1;
                $newHead->save();
                $copiedCount++;
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => "Successfully copied {$copiedCount} fee heads!"
            ]);
        }

        return redirect()->back()->with('message', "Successfully copied {$copiedCount} fee heads!");
    }

    /**
     * Check if a fee head name is a protected system core head (Tuition Fees / Previous Dues)
     */
    protected function isCoreSystemHeadName($name)
    {
        if (empty($name)) return false;
        $lower = strtolower(trim($name));
        return strpos($lower, 'tuition') !== false ||
               strpos($lower, 'tution') !== false ||
               strpos($lower, 'previous dues') !== false ||
               strpos($lower, 'prev dues') !== false ||
               strpos($lower, 'previous fee') !== false ||
               strpos($lower, 'prev fee') !== false;
    }

    /**
     * Save Common Fee Head Name Master (Multi-Batch Applicable)
     */
    public function saveCommonStructure(Request $request)
    {
        $branchId = $this->getBranchId();

        $request->validate([
            'head_name' => 'required|string|max:100',
        ]);

        $headName = trim($request->head_name);
        $groupType = trim($request->get('group_type') ?: ($request->get('fee_type') ?: 'Tuition'));
        $description = trim($request->get('description', ''));
        $userId = Session::get('id') ?: 1;
        $dynamicSession = helper::resolveCurrentFinancialSession($branchId);
        $sessionId = $dynamicSession->id ?? 1;

        // User requirement: Tuition Fees and Previous Dues are permanent core heads (non-editable, non-deletable)
        if ($this->isCoreSystemHeadName($headName)) {
            $msg = "Fee Head '{$headName}' is a protected System Core fee head and cannot be edited, modified or overwritten.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        try {
            $head = CollegeFeeHead::where('branch_id', $branchId)
                ->where('name', $headName)
                ->first();

            if (!$head) {
                $head = new CollegeFeeHead();
                $head->branch_id = $branchId;
                $head->name = $headName;
                $head->fee_type = $groupType;
                $head->description = $description;
                $head->status = 1;
                $head->created_by = $userId;
                $head->save();
            } else {
                $head->fee_type = $groupType;
                if (!empty($description)) {
                    $head->description = $description;
                }
                $head->deleted_at = null; // in case it was soft-deleted
                $head->save();
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Fee Head '{$headName}' has been saved successfully.",
                    'head' => [
                        'id' => $head->id,
                        'head_name' => $head->name,
                        'fee_type' => $head->fee_type,
                        'group_type' => $head->fee_type,
                        'description' => $head->description
                    ]
                ]);
            }

            return redirect()->back()->with('message', "Fee Head '{$headName}' has been saved successfully.");

        } catch (\Exception $e) {
            Log::error('Error saving common fee head: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to save fee head: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to save fee head.');
        }
    }

    /**
     * Get Common Fee Heads Library
     */
    public function getCommonStructures(Request $request)
    {
        $branchId = $this->getBranchId();

        $heads = CollegeFeeHead::where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->where('name', 'NOT LIKE', '%previous%')
            ->where('name', 'NOT LIKE', '%tuition%')
            ->where('name', 'NOT LIKE', '%tution%')
            ->orderBy('name', 'ASC')
            ->get();

        $commonHeads = $heads->map(function ($h) {
            return [
                'id' => $h->id,
                'head_name' => $h->name,
                'fee_type' => $h->fee_type ?: 'Tuition',
                'group_type' => $h->fee_type ?: 'Tuition',
                'description' => $h->description ?: '',
                'created_at' => $h->created_at ? $h->created_at->format('d-M-Y') : ''
            ];
        });

        return response()->json([
            'status' => 'success',
            'common_heads' => $commonHeads,
            'count' => count($commonHeads)
        ]);
    }

    /**
     * Delete Common Fee Head
     */
    public function deleteCommonHead($id)
    {
        $branchId = $this->getBranchId();
        $head = CollegeFeeHead::where('branch_id', $branchId)->find($id);
        if ($head) {
            if ($this->isCoreSystemHeadName($head->name)) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Fee Head '{$head->name}' is a protected System Core fee head and cannot be deleted."
                ], 422);
            }
            $head->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Fee Head has been removed successfully.'
        ]);
    }

    /**
     * Auto-Assign Fee Structure to Enrolled Students
     */
    public function autoAssignStructure(Request $request)
    {
        $branchId = $this->getBranchId();
        $courseId = $request->course_id;
        $semesterNo = (int)$request->semester_no;
        $batch = $request->batch;

        $structures = CollegeFeeStructure::where('branch_id', $branchId)
            ->where('course_id', $courseId)
            ->where('semester_no', $semesterNo)
            ->when(!empty($batch), function($q) use ($batch) {
                $q->where('batch', $batch)->orWhereNull('batch');
            })
            ->get();

        if ($structures->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Please configure fee heads for this course and semester first!']);
        }

        $course = Course::find($courseId);
        $courseName = $course->name ?? '';

        // Find active students in this course for current branch
        $students = Admission::where('branch_id', $branchId)
            ->where('status', 1)
            ->where('school', 1)
            ->where(function($q) use ($courseId, $courseName) {
                $q->where('course', $courseName)
                  ->orWhereHas('ClassTypes', function($sub) use ($courseId) {
                      $sub->where('course_id', $courseId);
                  });
            })
            ->when(!empty($batch), function($q) use ($batch) {
                $q->where('batch', $batch);
            })
            ->get();

        $assignedCount = 0;
        foreach ($students as $stu) {
            // Check if already assigned for this semester
            $hasAssignment = CollegeFeeAssignment::where('branch_id', $branchId)
                ->where('student_id', $stu->id)
                ->where('semester_no', $semesterNo)
                ->exists();

            if (!$hasAssignment) {
                $isGirl = in_array((int)$stu->gender_id, [2, 3]); // Female
                foreach ($structures as $head) {
                    $amount = $isGirl ? (float)$head->amount_girls : (float)$head->amount_boys;
                    if ($amount <= 0 && $head->amount_boys > 0) {
                        $amount = (float)$head->amount_boys;
                    }

                    $assign = new CollegeFeeAssignment();
                    $assign->branch_id = $branchId;
                    $assign->student_id = $stu->id;
                    $assign->course_id = $courseId;
                    $assign->semester_no = $semesterNo;
                    $assign->structure_id = $head->id;
                    $assign->head_name = $head->head_name;
                    $assign->gross_amount = $amount;
                    $assign->discount_amount = 0;
                    $assign->net_amount = $amount;
                    $assign->paid_amount = 0;
                    $assign->due_amount = $amount;
                    $assign->status = $amount > 0 ? 'Unpaid' : 'Paid';
                    $assign->created_by = Session::get('id') ?: 1;
                    $assign->save();
                }
                $assignedCount++;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Successfully assigned fee structure to {$assignedCount} students!"
        ]);
    }


    /**
     * Get Complete Student Ledger & Fees Details
     */
    public function getStudentLedger($studentId)
    {
        $branchId = $this->getBranchId();

        $student = Admission::with('classType')
            ->where('branch_id', $branchId)
            ->findOrFail($studentId);

        $courseId = $student->classType->course_id ?? Course::where('branch_id', $branchId)->where('name', $student->course)->value('id') ?? 1;
        $course = Course::find($courseId);
        $courseName = $course->name ?? $student->course ?? 'Course';
        $currentSemNo = $this->parseSemesterNumber($student->classType->name ?? '');

        // 1. Fee Assignments grouped by semester
        $assignments = CollegeFeeAssignment::where('branch_id', $branchId)
            ->where('student_id', $studentId)
            ->orderBy('semester_no', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();

        // 2. Fee Receipts
        $receipts = CollegeFeeReceipt::with('cashier')
            ->where('branch_id', $branchId)
            ->where('student_id', $studentId)
            ->orderBy('id', 'DESC')
            ->get();

        // 3. Totals
        $totalGross = (float)$assignments->sum('gross_amount');
        $totalDiscount = (float)$assignments->sum('discount_amount');
        $totalNet = (float)$assignments->sum('net_amount');
        $totalPaid = (float)$receipts->sum('amount_paid');
        $totalDues = (float)$assignments->sum('due_amount');
        $netBalance = $totalNet - $totalPaid; // if negative -> advance

        // Check if current semester has any assignments
        $currentSemHasAssignments = $assignments->where('semester_no', $currentSemNo)->isNotEmpty();

        return response()->json([
            'status' => 'success',
            'student' => [
                'id' => $student->id,
                'admissionNo' => $student->admissionNo,
                'name' => trim("{$student->first_name} {$student->last_name}"),
                'father_name' => $student->father_name,
                'mother_name' => $student->mother_name,
                'mobile' => $student->mobile,
                'course_id' => $courseId,
                'course_name' => $courseName,
                'current_semester' => $currentSemNo,
                'batch' => $student->batch ?: date('Y'),
                'gender_id' => $student->gender_id,
                'photo' => $student->image ? env('IMAGE_SHOW_PATH').'student_profile/'.$student->image : null
            ],
            'summary' => [
                'total_gross' => $totalGross,
                'total_discount' => $totalDiscount,
                'total_net' => $totalNet,
                'total_paid' => $totalPaid,
                'total_dues' => max(0, $totalDues),
                'advance_balance' => $netBalance < 0 ? abs($netBalance) : 0,
                'current_sem_has_assignments' => $currentSemHasAssignments
            ],
            'assignments_by_sem' => $assignments->groupBy('semester_no'),
            'receipts' => $receipts
        ]);
    }

    /**
     * 4. Collect Fee Payment & Generate Receipt
     */
        /**
     * Get real-time student data, fee heads with due amounts, and receipt history for collection modal
     */
    public function getStudentCollectData(Request $request)
    {
        $branchId = $this->getBranchId();
        $studentId = (int)$request->student_id;
        $semesterNo = (int)($request->semester_no ?: 1);

        $student = Admission::where('branch_id', $branchId)->findOrFail($studentId);
        $course = Course::find($student->course_id) ?? Course::where('name', $student->course)->first();
        $courseName = $course ? $course->name : ($student->course ?: 'Course');
        $batch = $student->batch ?: date('Y');
        $rollNo = $student->admissionNo ?: ($student->userName ?: ('BC' . substr($batch, -2) . '-' . str_pad($student->id, 3, '0', STR_PAD_LEFT)));

        // 1. Fetch assigned fee heads for this student & semester (excluding invalid non-positive carried dues)
        $assignments = CollegeFeeAssignment::where('branch_id', $branchId)
            ->where('student_id', $student->id)
            ->where('semester_no', $semesterNo)
            ->where(function($q) {
                $q->where('is_carried_dues', 0)
                  ->orWhere('gross_amount', '!=', 0);
            })
            ->orderBy('id', 'ASC')
            ->get();

        $headsData = [];
        $totalNet = 0;
        $totalPaid = 0;
        $totalDue = 0;

        foreach ($assignments as $a) {
            $net = (float)$a->net_amount;
            $paid = (float)$a->paid_amount;
            $due = (float)$a->due_amount;

            $totalNet += $net;
            $totalPaid += $paid;
            $totalDue += $due;

            $headsData[] = [
                'assignment_id' => $a->id,
                'head_name' => $a->head_name,
                'gross' => (float)$a->gross_amount,
                'discount' => (float)$a->discount_amount,
                'net' => $net,
                'paid' => $paid,
                'due' => $due,
                'status' => $a->status
            ];
        }

        // 2. Fetch receipts history for this student & semester (including cancelled receipts)
        $receipts = CollegeFeeReceipt::withTrashed()
            ->with('items')
            ->where('branch_id', $branchId)
            ->where('student_id', $student->id)
            ->where('semester_no', $semesterNo)
            ->orderBy('id', 'DESC')
            ->get();

        $receiptsData = [];
        foreach ($receipts as $r) {
            $itemsSummary = [];
            foreach ($r->items as $item) {
                $itemsSummary[] = [
                    'head_name' => $item->head_name,
                    'amount' => (float)$item->amount_allocated
                ];
            }

            $isCancelled = !is_null($r->deleted_at);
            $receiptsData[] = [
                'id' => $r->id,
                'receipt_no' => $r->receipt_no,
                'payment_date' => date('d M Y', strtotime($r->payment_date)),
                'payment_mode' => $r->payment_mode ?: 'Cash',
                'transaction_id' => $r->transaction_id ?: '',
                'amount_paid' => (float)$r->amount_paid,
                'items' => $itemsSummary,
                'is_cancelled' => $isCancelled,
                'cancelled_at' => $isCancelled ? date('d M Y, h:i A', strtotime($r->deleted_at)) : null,
                'print_url' => route('college.fees.receipt', $r->id),
                'download_url' => route('college.fees.receipt_download', $r->id)
            ];
        }

        // 3. Next Receipt No preview (includes cancelled receipts)
        $year = date('Y');
        $nextReceiptNo = CollegeFeeReceipt::generateNextReceiptNo($year);

        // 4. Check if student has progressed to a higher semester
        $isProgressed = CollegeFeeAssignment::where('branch_id', $branchId)
            ->where('student_id', $student->id)
            ->where('semester_no', '>', $semesterNo)
            ->exists();

        $nextSemester = null;
        $transferredDues = 0;
        if ($isProgressed) {
            $nextSemester = CollegeFeeAssignment::where('branch_id', $branchId)
                ->where('student_id', $student->id)
                ->where('semester_no', '>', $semesterNo)
                ->min('semester_no');
            $transferredDues = round($totalNet - $totalPaid, 2);
            $totalDue = 0;
            $status = 'Progressed';

            foreach ($headsData as &$hd) {
                $hd['due'] = 0;
                $hd['status'] = 'Progressed';
            }
            unset($hd);
        } elseif ($assignments->isEmpty()) {
            $status = 'Unassigned';
        } elseif ($totalDue <= 0) {
            $status = 'Paid';
        } elseif ($totalPaid > 0) {
            $status = 'Partial';
        } else {
            $status = 'Unpaid';
        }

        return response()->json([
            'status' => 'success',
            'student' => [
                'id' => $student->id,
                'name' => trim("{$student->first_name} {$student->last_name}") ?: ($student->userName ?: 'Student'),
                'roll_no' => $rollNo,
                'course_name' => $courseName,
                'batch' => $batch,
                'semester_no' => $semesterNo,
                'status' => $status,
                'is_progressed' => $isProgressed,
                'next_semester' => $nextSemester,
                'transferred_dues' => $transferredDues
            ],
            'heads' => $headsData,
            'receipts' => $receiptsData,
            'next_receipt_no' => $nextReceiptNo,
            'totals' => [
                'net' => $totalNet,
                'paid' => $totalPaid,
                'due' => $isProgressed ? 0 : $totalDue
            ]
        ]);
    }

    /**
     * 4. Collect Fee Payment & Generate Receipt (Head-Specific & Multi-Mode Splits)
     */
    public function collectPayment(Request $request)
    {
        $branchId = $this->getBranchId();

        $request->validate([
            'student_id' => 'required|integer',
            'payment_mode' => 'required|string',
            'payment_date' => 'required|date',
        ]);

        $student = Admission::with('classType')
            ->where('branch_id', $branchId)
            ->findOrFail($request->student_id);

        $courseId = $student->classType->course_id ?? Course::where('branch_id', $branchId)->where('name', $student->course)->value('id') ?? 1;
        $currentSemNo = (int)($request->semester_no ?: 1);

        // Security check: cannot collect payment for a progressed / past semester
        $hasProgressed = CollegeFeeAssignment::where('branch_id', $branchId)
            ->where('student_id', $student->id)
            ->where('semester_no', '>', $currentSemNo)
            ->exists();

        if ($hasProgressed) {
            $nextSem = CollegeFeeAssignment::where('branch_id', $branchId)
                ->where('student_id', $student->id)
                ->where('semester_no', '>', $currentSemNo)
                ->min('semester_no');

            return response()->json([
                'status' => 'error',
                'message' => "Fee collection is closed for Semester {$currentSemNo}. This student has progressed to Semester {$nextSem} and outstanding balances were transferred to Previous Dues."
            ], 422);
        }

        // Check if head-specific allocations were submitted
        $headsInput = (array)$request->heads;
        $headAllocations = [];
        $totalAmountPaid = 0;

        if (!empty($headsInput)) {
            foreach ($headsInput as $h) {
                $assignId = (int)($h['assignment_id'] ?? 0);
                $amt = max(0, (float)($h['amount'] ?? 0));
                if ($assignId > 0 && $amt > 0) {
                    $headAllocations[$assignId] = $amt;
                    $totalAmountPaid += $amt;
                }
            }
        } else {
            $totalAmountPaid = max(0, (float)$request->amount_paid);
        }

        if ($totalAmountPaid <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please enter a valid collection amount greater than zero.'
            ], 422);
        }

        // Dynamic financial session resolution
        $dynamicSession = helper::resolveCurrentFinancialSession($branchId);

        DB::beginTransaction();
        try {
            // Generate sequential receipt number RCPT-YYYY-XXXX (never reuses cancelled numbers)
            $year = date('Y', strtotime($request->payment_date));
            $receiptNo = CollegeFeeReceipt::generateNextReceiptNo($year);

            // Multi-mode payment splits support (e.g. 400 UPI + 200 Cash)
            $paymentMode = $request->payment_mode ?: 'Cash';
            $transactionId = $request->transaction_id ?: null;
            $splitsInput = (array)$request->payment_splits;

            if (!empty($splitsInput)) {
                $splitParts = [];
                $splitRefs = [];
                foreach ($splitsInput as $sp) {
                    $m = trim($sp['mode'] ?? 'Cash');
                    $a = max(0, (float)($sp['amount'] ?? 0));
                    $r = trim($sp['ref'] ?? '');
                    if ($a > 0) {
                        $splitParts[] = "{$m} (₹" . number_format($a, 2) . ")";
                        if (!empty($r)) {
                            $splitRefs[] = "{$m}: {$r}";
                        }
                    }
                }
                if (!empty($splitParts)) {
                    $paymentMode = 'Split: ' . implode(' + ', $splitParts);
                    if (!empty($splitRefs)) {
                        $transactionId = implode(' | ', $splitRefs);
                    }
                }
            }

            $receipt = new CollegeFeeReceipt();
            $receipt->receipt_no = $receiptNo;
            $receipt->branch_id = $branchId;
            $receipt->session_id = $dynamicSession ? $dynamicSession->id : 1;
            $receipt->student_id = $student->id;
            $receipt->course_id = $courseId;
            $receipt->semester_no = $currentSemNo;
            $receipt->payment_date = $request->payment_date;
            $receipt->payment_mode = $paymentMode;
            $receipt->transaction_id = $transactionId;
            $receipt->bank_name = $request->bank_name ?: null;
            $receipt->amount_paid = $totalAmountPaid;
            $receipt->discount_given = (float)($request->discount_given ?: 0);
            $receipt->remark = $request->remark ?: null;
            $receipt->cashier_id = Session::get('id') ?: 1;
            $receipt->save();

            // Allocation: Head-Specific or FIFO
            // USER RULE: Advance / surplus amount can ONLY be added to Tuition Fee, NEVER to Previous Dues or other heads!
            if (!empty($headAllocations)) {
                $surplusForTuition = 0;
                $tuitionAssign = CollegeFeeAssignment::where('branch_id', $branchId)
                    ->where('student_id', $student->id)
                    ->where('semester_no', $currentSemNo)
                    ->where(function($q) {
                        $q->where('head_name', 'LIKE', '%Tuition%')
                          ->orWhere('head_name', 'LIKE', '%Tution%');
                    })
                    ->first();

                foreach ($headAllocations as $assignId => $allocatedAmt) {
                    $assign = CollegeFeeAssignment::where('branch_id', $branchId)
                        ->where('student_id', $student->id)
                        ->findOrFail($assignId);

                    $isTuition = (stripos($assign->head_name, 'tuition') !== false || stripos($assign->head_name, 'tution') !== false);
                    $due = max(0, (float)$assign->due_amount);

                    // For non-tuition heads (Previous Dues, Exam Fee, etc.), cap allocation to due amount
                    if (!$isTuition && $allocatedAmt > $due) {
                        $excess = $allocatedAmt - $due;
                        $allocatedAmt = $due;
                        $surplusForTuition += $excess;
                    }

                    if ($allocatedAmt > 0) {
                        $assign->paid_amount += $allocatedAmt;
                        $assign->due_amount = $assign->net_amount - $assign->paid_amount;
                        $assign->status = $assign->due_amount <= 0 ? 'Paid' : 'Partial';
                        $assign->save();

                        // Save receipt item specifically for this head
                        $item = new CollegeFeeReceiptItem();
                        $item->receipt_id = $receipt->id;
                        $item->assignment_id = $assign->id;
                        $item->head_name = $assign->head_name;
                        $item->amount_allocated = $allocatedAmt;
                        $item->save();
                    }
                }

                // If non-tuition head had excess, route that excess advance ONLY to Tuition Fee!
                if ($surplusForTuition > 0) {
                    if ($tuitionAssign) {
                        $tuitionAssign->paid_amount += $surplusForTuition;
                        $tuitionAssign->due_amount = $tuitionAssign->net_amount - $tuitionAssign->paid_amount;
                        $tuitionAssign->status = $tuitionAssign->due_amount <= 0 ? 'Paid' : 'Partial';
                        $tuitionAssign->save();

                        $tItem = CollegeFeeReceiptItem::where('receipt_id', $receipt->id)
                            ->where('assignment_id', $tuitionAssign->id)
                            ->first();
                        if ($tItem) {
                            $tItem->amount_allocated += $surplusForTuition;
                            $tItem->save();
                        } else {
                            $item = new CollegeFeeReceiptItem();
                            $item->receipt_id = $receipt->id;
                            $item->assignment_id = $tuitionAssign->id;
                            $item->head_name = $tuitionAssign->head_name;
                            $item->amount_allocated = $surplusForTuition;
                            $item->save();
                        }
                    } else {
                        $item = new CollegeFeeReceiptItem();
                        $item->receipt_id = $receipt->id;
                        $item->assignment_id = null;
                        $item->head_name = "Advance Fee Deposit (Tuition)";
                        $item->amount_allocated = $surplusForTuition;
                        $item->save();
                    }
                }
            } else {
                // FIFO fallback allocation across unpaid assignments
                $unpaidAssignments = CollegeFeeAssignment::where('branch_id', $branchId)
                    ->where('student_id', $student->id)
                    ->where('due_amount', '>', 0)
                    ->orderBy('semester_no', 'ASC')
                    ->orderBy('id', 'ASC')
                    ->get();

                $remainingToAllocate = $totalAmountPaid;

                foreach ($unpaidAssignments as $assign) {
                    if ($remainingToAllocate <= 0) break;

                    $due = (float)$assign->due_amount;
                    if ($due <= 0) continue;

                    $allocated = min($due, $remainingToAllocate);

                    $assign->paid_amount += $allocated;
                    $assign->due_amount = $assign->net_amount - $assign->paid_amount;
                    $assign->status = $assign->due_amount <= 0 ? 'Paid' : 'Partial';
                    $assign->save();

                    // Save receipt item
                    $item = new CollegeFeeReceiptItem();
                    $item->receipt_id = $receipt->id;
                    $item->assignment_id = $assign->id;
                    $item->head_name = $assign->head_name;
                    $item->amount_allocated = $allocated;
                    $item->save();

                    $remainingToAllocate -= $allocated;
                }

                // If any surplus amount remains (advance payment), route strictly to Tuition Fee!
                if ($remainingToAllocate > 0) {
                    $tuitionAssign = CollegeFeeAssignment::where('branch_id', $branchId)
                        ->where('student_id', $student->id)
                        ->where('semester_no', $currentSemNo)
                        ->where(function($q) {
                            $q->where('head_name', 'LIKE', '%Tuition%')
                              ->orWhere('head_name', 'LIKE', '%Tution%');
                        })
                        ->first();

                    if ($tuitionAssign) {
                        $tuitionAssign->paid_amount += $remainingToAllocate;
                        $tuitionAssign->due_amount = $tuitionAssign->net_amount - $tuitionAssign->paid_amount;
                        $tuitionAssign->status = 'Paid';
                        $tuitionAssign->save();

                        $tItem = CollegeFeeReceiptItem::where('receipt_id', $receipt->id)
                            ->where('assignment_id', $tuitionAssign->id)
                            ->first();
                        if ($tItem) {
                            $tItem->amount_allocated += $remainingToAllocate;
                            $tItem->save();
                        } else {
                            $item = new CollegeFeeReceiptItem();
                            $item->receipt_id = $receipt->id;
                            $item->assignment_id = $tuitionAssign->id;
                            $item->head_name = $tuitionAssign->head_name;
                            $item->amount_allocated = $remainingToAllocate;
                            $item->save();
                        }
                    } else {
                        $item = new CollegeFeeReceiptItem();
                        $item->receipt_id = $receipt->id;
                        $item->assignment_id = null;
                        $item->head_name = "Advance Fee Deposit (Tuition)";
                        $item->amount_allocated = $remainingToAllocate;
                        $item->save();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment of ₹' . number_format($totalAmountPaid, 2) . ' collected successfully!',
                'receipt_id' => $receipt->id,
                'receipt_no' => $receipt->receipt_no,
                'amount_paid' => $totalAmountPaid,
                'print_url' => route('college.fees.receipt', $receipt->id),
                'download_url' => route('college.fees.receipt_download', $receipt->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error collecting payment: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Payment collection failed: ' . $e->getMessage()
            ], 500);
        }
    }

public function printReceipt($id)
    {
        $branchId = $this->getBranchId();

        $receipt = CollegeFeeReceipt::withTrashed()
            ->with(['student', 'course', 'cashier', 'items'])
            ->where('branch_id', $branchId)
            ->findOrFail($id);

        $setting = Setting::where('branch_id', $receipt->branch_id)->first() ?: Setting::first();
        $student = $receipt->student;

        // Number to words converter
        $amountInWords = $this->numberToWords((int)$receipt->amount_paid);

        $pdf = \PDF::loadView('college_fees.receipt_pdf', compact('receipt', 'setting', 'student', 'amountInWords'));
        $pdf->setPaper('a5', 'landscape');

        return $pdf->stream('Receipt-' . $receipt->receipt_no . '.pdf');
    }

    /**
     * 6. Batch Semester Progression
     */
        /**
     * 5b. Download Receipt as PDF
     */
    public function downloadReceipt($id)
    {
        $branchId = $this->getBranchId();

        $receipt = CollegeFeeReceipt::withTrashed()
            ->with(['student', 'course', 'cashier', 'items'])
            ->where('branch_id', $branchId)
            ->findOrFail($id);

        $setting = Setting::where('branch_id', $receipt->branch_id)->first() ?: Setting::first();
        $student = $receipt->student;
        $amountInWords = $this->numberToWords((int)$receipt->amount_paid);

        $pdf = \PDF::loadView('college_fees.receipt_pdf', compact('receipt', 'setting', 'student', 'amountInWords'));
        $pdf->setPaper('a5', 'landscape');

        return $pdf->download('Receipt-' . $receipt->receipt_no . '.pdf');
    }

    /**
     * 5c. Cancel Fee Receipt & Revert Payments
     */
    public function cancelReceipt(Request $request)
    {
        $branchId = $this->getBranchId();
        $receiptId = (int)$request->receipt_id;

        $receipt = CollegeFeeReceipt::withTrashed()
            ->with('items')
            ->where('branch_id', $branchId)
            ->findOrFail($receiptId);

        if ($receipt->trashed()) {
            return response()->json(['status' => 'error', 'message' => 'Cancelled receipts cannot be edited or modified.'], 422);
        }

        if (CollegeFeeAssignment::where('branch_id', $branchId)->where('student_id', $receipt->student_id)->where('semester_no', '>', $receipt->semester_no)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Cannot cancel receipts for a completed/progressed semester.'], 422);
        }

        $studentId = $receipt->student_id;
        $semesterNo = $receipt->semester_no;
        $receiptNo = $receipt->receipt_no;
        $revertedAmt = (float)$receipt->amount_paid;

        DB::beginTransaction();
        try {
            // Revert payments on fee assignments
            foreach ($receipt->items as $item) {
                if ($item->assignment_id) {
                    $assign = CollegeFeeAssignment::where('branch_id', $branchId)
                        ->where('student_id', $studentId)
                        ->find($item->assignment_id);

                    if ($assign) {
                        $assign->paid_amount -= (float)$item->amount_allocated;
                        $assign->due_amount = $assign->net_amount - $assign->paid_amount;
                        $assign->status = ($assign->paid_amount <= 0) 
                            ? 'Unpaid' 
                            : ($assign->due_amount <= 0 ? 'Paid' : 'Partial');
                        $assign->save();
                    }
                }
            }

            // Soft delete the receipt (KEEP receipt items intact for history & printing with CANCELLED watermark)
            $receipt->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Receipt {$receiptNo} (₹" . number_format($revertedAmt, 2) . ") cancelled successfully. Payments reverted and outstanding recalculated.",
                'student_id' => $studentId,
                'semester_no' => $semesterNo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to cancel receipt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 5d. Get Single Receipt Details for Modification
     */
    public function getReceiptEditData($id)
    {
        $branchId = $this->getBranchId();
        $receipt = CollegeFeeReceipt::withTrashed()
            ->with('items')
            ->where('branch_id', $branchId)
            ->findOrFail($id);

        if ($receipt->trashed()) {
            return response()->json(['status' => 'error', 'message' => 'Cancelled receipts cannot be edited or modified.'], 422);
        }

        $items = [];
        foreach ($receipt->items as $it) {
            $items[] = [
                'assignment_id' => $it->assignment_id,
                'head_name' => $it->head_name,
                'amount' => (float)$it->amount_allocated
            ];
        }

        return response()->json([
            'status' => 'success',
            'receipt' => [
                'id' => $receipt->id,
                'receipt_no' => $receipt->receipt_no,
                'payment_date' => $receipt->payment_date,
                'payment_mode' => $receipt->payment_mode,
                'transaction_id' => $receipt->transaction_id ?: '',
                'amount_paid' => (float)$receipt->amount_paid,
                'remark' => $receipt->remark ?: '',
                'items' => $items
            ]
        ]);
    }

    /**
     * 5e. Update / Modify Receipt & Recalculate Outstanding
     */
    public function updateReceipt(Request $request)
    {
        $branchId = $this->getBranchId();
        $receiptId = (int)$request->receipt_id;

        $request->validate([
            'receipt_id' => 'required|integer',
            'payment_mode' => 'required|string',
            'payment_date' => 'required|date',
        ]);

        $receipt = CollegeFeeReceipt::with('items')
            ->where('branch_id', $branchId)
            ->findOrFail($receiptId);

        $studentId = $receipt->student_id;
        $semesterNo = $receipt->semester_no;

        $headsInput = (array)$request->heads;
        $headAllocations = [];
        $totalAmountPaid = 0;

        foreach ($headsInput as $h) {
            $assignId = (int)($h['assignment_id'] ?? 0);
            $amt = max(0, (float)($h['amount'] ?? 0));
            if ($assignId > 0 && $amt > 0) {
                $headAllocations[$assignId] = $amt;
                $totalAmountPaid += $amt;
            }
        }

        if ($totalAmountPaid <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Total payment amount must be greater than zero.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Step 1: Revert all old allocations from this receipt
            foreach ($receipt->items as $item) {
                if ($item->assignment_id) {
                    $assign = CollegeFeeAssignment::where('branch_id', $branchId)
                        ->where('student_id', $studentId)
                        ->find($item->assignment_id);

                    if ($assign) {
                        $assign->paid_amount -= (float)$item->amount_allocated;
                        $assign->due_amount = $assign->net_amount - $assign->paid_amount;
                        $assign->status = ($assign->paid_amount <= 0) 
                            ? 'Unpaid' 
                            : ($assign->due_amount <= 0 ? 'Paid' : 'Partial');
                        $assign->save();
                    }
                }
            }

            // Remove old items
            CollegeFeeReceiptItem::where('receipt_id', $receipt->id)->delete();

            // Step 2: Apply new allocations (Advance belongs only to Tuition Fee)
            $surplusForTuition = 0;
            $tuitionAssign = CollegeFeeAssignment::where('branch_id', $branchId)
                ->where('student_id', $studentId)
                ->where('semester_no', $semesterNo)
                ->where(function($q) {
                    $q->where('head_name', 'LIKE', '%Tuition%')
                      ->orWhere('head_name', 'LIKE', '%Tution%');
                })
                ->first();

            foreach ($headAllocations as $assignId => $newAmt) {
                $assign = CollegeFeeAssignment::where('branch_id', $branchId)
                    ->where('student_id', $studentId)
                    ->findOrFail($assignId);

                $isTuition = (stripos($assign->head_name, 'tuition') !== false || stripos($assign->head_name, 'tution') !== false);
                $availableDue = max(0, (float)$assign->net_amount - (float)$assign->paid_amount);

                if (!$isTuition && $newAmt > $availableDue) {
                    $excess = $newAmt - $availableDue;
                    $newAmt = $availableDue;
                    $surplusForTuition += $excess;
                }

                if ($newAmt > 0) {
                    $assign->paid_amount += $newAmt;
                    $assign->due_amount = $assign->net_amount - $assign->paid_amount;
                    $assign->status = ($assign->due_amount <= 0) ? 'Paid' : 'Partial';
                    $assign->save();

                    $item = new CollegeFeeReceiptItem();
                    $item->receipt_id = $receipt->id;
                    $item->assignment_id = $assign->id;
                    $item->head_name = $assign->head_name;
                    $item->amount_allocated = $newAmt;
                    $item->save();
                }
            }

            if ($surplusForTuition > 0) {
                if ($tuitionAssign) {
                    $tuitionAssign->paid_amount += $surplusForTuition;
                    $tuitionAssign->due_amount = $tuitionAssign->net_amount - $tuitionAssign->paid_amount;
                    $tuitionAssign->status = ($tuitionAssign->due_amount <= 0) ? 'Paid' : 'Partial';
                    $tuitionAssign->save();

                    $tItem = CollegeFeeReceiptItem::where('receipt_id', $receipt->id)
                        ->where('assignment_id', $tuitionAssign->id)
                        ->first();
                    if ($tItem) {
                        $tItem->amount_allocated += $surplusForTuition;
                        $tItem->save();
                    } else {
                        $item = new CollegeFeeReceiptItem();
                        $item->receipt_id = $receipt->id;
                        $item->assignment_id = $tuitionAssign->id;
                        $item->head_name = $tuitionAssign->head_name;
                        $item->amount_allocated = $surplusForTuition;
                        $item->save();
                    }
                } else {
                    $item = new CollegeFeeReceiptItem();
                    $item->receipt_id = $receipt->id;
                    $item->assignment_id = null;
                    $item->head_name = "Advance Fee Deposit (Tuition)";
                    $item->amount_allocated = $surplusForTuition;
                    $item->save();
                }
            }

            // Step 3: Handle multi-mode split or single mode
            $paymentMode = $request->payment_mode ?: 'Cash';
            $transactionId = $request->transaction_id ?: null;
            $splitsInput = (array)$request->payment_splits;

            if (!empty($splitsInput)) {
                $splitParts = [];
                $splitRefs = [];
                foreach ($splitsInput as $sp) {
                    $m = trim($sp['mode'] ?? 'Cash');
                    $a = max(0, (float)($sp['amount'] ?? 0));
                    $r = trim($sp['ref'] ?? '');
                    if ($a > 0) {
                        $splitParts[] = "{$m} (₹" . number_format($a, 2) . ")";
                        if (!empty($r)) {
                            $splitRefs[] = "{$m}: {$r}";
                        }
                    }
                }
                if (!empty($splitParts)) {
                    $paymentMode = 'Split: ' . implode(' + ', $splitParts);
                    if (!empty($splitRefs)) {
                        $transactionId = implode(' | ', $splitRefs);
                    }
                }
            }

            $receipt->payment_date = $request->payment_date;
            $receipt->payment_mode = $paymentMode;
            $receipt->transaction_id = $transactionId;
            $receipt->amount_paid = $totalAmountPaid;
            if ($request->filled('remark')) {
                $receipt->remark = $request->remark;
            }
            $receipt->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Receipt {$receipt->receipt_no} updated successfully! Outstanding amounts recalculated.",
                'receipt_id' => $receipt->id,
                'student_id' => $studentId,
                'semester_no' => $semesterNo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update receipt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detect Active Semester for selected Course and Batch
     */
    public function detectBatchActiveSemester(Request $request)
    {
        $branchId = $this->getBranchId();
        $courseId = (int)$request->get('course_id');
        $batch = $request->get('batch', date('Y'));

        $course = Course::find($courseId);
        $maxSemesters = $course ? (int)($course->total_semester ?: 6) : 6;
        $courseName = $course->name ?? '';

        $admissions = Admission::with('classType')
            ->where('branch_id', $branchId)
            ->where('school', 1)
            ->where(function($q) use ($courseId, $courseName) {
                $q->where('course', $courseName)
                  ->orWhereHas('ClassTypes', function($sub) use ($courseId) {
                      $sub->where('course_id', $courseId);
                  });
            })
            ->where('batch', $batch)
            ->get();

        $totalStudents = $admissions->count();
        $detectedSem = 1;

        if ($admissions->isNotEmpty()) {
            $semCounts = [];
            foreach ($admissions as $s) {
                $clsName = $s->classType ? $s->classType->name : '';
                if (preg_match('/(\d+)\s*(st|nd|rd|th)?\s*Sem/i', $clsName, $m)) {
                    $sNo = (int)$m[1];
                } elseif (preg_match('/Sem(?:ester)?\s*(\d+)/i', $clsName, $m)) {
                    $sNo = (int)$m[1];
                } else {
                    $sNo = 1;
                }
                $semCounts[$sNo] = ($semCounts[$sNo] ?? 0) + 1;
            }
            if (!empty($semCounts)) {
                arsort($semCounts);
                $detectedSem = (int)key($semCounts);
            }
        }

        $stuIds = $admissions->pluck('id')->toArray();
        if (!empty($stuIds)) {
            $maxFeeSem = (int)CollegeFeeAssignment::where('branch_id', $branchId)
                ->whereIn('student_id', $stuIds)
                ->max('semester_no');
            if ($maxFeeSem > $detectedSem) {
                $detectedSem = $maxFeeSem;
            }
        }

        if ($detectedSem < 1) $detectedSem = 1;
        if ($detectedSem > $maxSemesters) $detectedSem = $maxSemesters;

        $targetSem = min($detectedSem + 1, $maxSemesters);

        return response()->json([
            'status' => 'success',
            'course_id' => $courseId,
            'course_name' => $courseName,
            'batch' => $batch,
            'total_students' => $totalStudents,
            'current_semester' => $detectedSem,
            'target_semester' => $targetSem,
            'max_semesters' => $maxSemesters,
            'is_final_semester' => ($detectedSem >= $maxSemesters)
        ]);
    }

    /**
     * 6. Semester Progression & Automated Fee Rollover
     */
    public function progression(Request $request)
    {
        $branchId = $this->getBranchId();

        $courses = Course::where('branch_id', $branchId)->orderBy('name', 'ASC')->get();
        if ($courses->isEmpty()) {
            $courses = Course::orderBy('name', 'ASC')->get();
        }

        $courseId = $request->get('course_id');
        $batch = $request->get('batch', date('Y'));
        $hasExplicitFrom = $request->has('from_semester');
        $fromSemester = (int)$request->get('from_semester', 1);
        $toSemester = (int)$request->get('to_semester', $fromSemester + 1);

        $students = collect();
        $targetStructures = collect();
        $summary = [
            'total' => 0,
            'active_total' => 0,
            'deactive_total' => 0,
            'upgraded' => 0,
            'pending' => 0,
            'cleared' => 0,
            'dues' => 0,
            'advance' => 0,
            'total_dues' => 0,
            'total_advance' => 0,
        ];

        $detectedSem = 1;
        $maxSemesters = 6;

        if ($courseId) {
            $course = Course::find($courseId);
            $maxSemesters = $course ? (int)($course->total_semester ?: 6) : 6;
            $cName = $course->name ?? '';

            // User requirement: Fetch ALL students (both active and deactive)
            $admissions = Admission::with('classType')
                ->where('branch_id', $branchId)
                ->where('school', 1)
                ->where(function($q) use ($courseId, $cName) {
                    $q->where('course', $cName)
                      ->orWhereHas('ClassTypes', function($sub) use ($courseId) {
                          $sub->where('course_id', $courseId);
                      });
                })
                ->when(!empty($batch), function($q) use ($batch) {
                    $q->where('batch', $batch);
                })
                ->get();

            // Auto-detect active semester of batch from student data if not explicitly provided
            if ($admissions->isNotEmpty()) {
                $semCounts = [];
                foreach ($admissions as $s) {
                    $clsName = $s->classType ? $s->classType->name : '';
                    if (preg_match('/(\d+)\s*(st|nd|rd|th)?\s*Sem/i', $clsName, $m)) {
                        $sNo = (int)$m[1];
                    } elseif (preg_match('/Sem(?:ester)?\s*(\d+)/i', $clsName, $m)) {
                        $sNo = (int)$m[1];
                    } else {
                        $sNo = 1;
                    }
                    $semCounts[$sNo] = ($semCounts[$sNo] ?? 0) + 1;
                }
                if (!empty($semCounts)) {
                    arsort($semCounts);
                    $detectedSem = (int)key($semCounts);
                }
            }

            $stuIdsSample = $admissions->pluck('id')->toArray();
            if (!empty($stuIdsSample)) {
                $maxFeeSem = (int)CollegeFeeAssignment::where('branch_id', $branchId)
                    ->whereIn('student_id', $stuIdsSample)
                    ->max('semester_no');
                if ($maxFeeSem > $detectedSem) {
                    $detectedSem = $maxFeeSem;
                }
            }
            if ($detectedSem < 1) $detectedSem = 1;
            if ($detectedSem > $maxSemesters) $detectedSem = $maxSemesters;

            if (!$hasExplicitFrom) {
                $fromSemester = $detectedSem;
                $toSemester = min($fromSemester + 1, $maxSemesters);
            }

            $studentIds = $admissions->pluck('id')->toArray();

            $netMap = [];
            $paidMap = [];
            $highestSemMap = [];
            $targetSemCountMap = [];
            $fromSemProgressedMap = [];

            if (!empty($studentIds)) {
                $netMap = CollegeFeeAssignment::where('branch_id', $branchId)
                    ->whereIn('student_id', $studentIds)
                    ->where('semester_no', $fromSemester)
                    ->groupBy('student_id')
                    ->selectRaw('student_id, SUM(net_amount) as total_net')
                    ->pluck('total_net', 'student_id')
                    ->toArray();

                $paidMap = CollegeFeeReceipt::where('branch_id', $branchId)
                    ->whereIn('student_id', $studentIds)
                    ->where('semester_no', $fromSemester)
                    ->groupBy('student_id')
                    ->selectRaw('student_id, SUM(amount_paid) as total_paid')
                    ->pluck('total_paid', 'student_id')
                    ->toArray();

                // High-performance bulk lookups for progression status detection
                $highestSemMap = CollegeFeeAssignment::where('branch_id', $branchId)
                    ->whereIn('student_id', $studentIds)
                    ->groupBy('student_id')
                    ->selectRaw('student_id, MAX(semester_no) as max_sem')
                    ->pluck('max_sem', 'student_id')
                    ->toArray();

                $targetSemCountMap = CollegeFeeAssignment::where('branch_id', $branchId)
                    ->whereIn('student_id', $studentIds)
                    ->where('semester_no', $toSemester)
                    ->groupBy('student_id')
                    ->selectRaw('student_id, COUNT(*) as cnt')
                    ->pluck('cnt', 'student_id')
                    ->toArray();

                $fromSemProgressedMap = CollegeFeeAssignment::where('branch_id', $branchId)
                    ->whereIn('student_id', $studentIds)
                    ->where('semester_no', $fromSemester)
                    ->where('status', 'Progressed')
                    ->pluck('student_id', 'student_id')
                    ->toArray();
            }

            foreach ($admissions as $stu) {
                $net = (float)($netMap[$stu->id] ?? 0);
                $paid = (float)($paidMap[$stu->id] ?? 0);
                $balance = $net - $paid; // > 0: Due, < 0: Advance, 0: Cleared
                $isDeactive = ((int)$stu->status !== 1);

                // Detection: Is this student already progressed / upgraded to target semester or higher?
                $hasTargetSem = !empty($targetSemCountMap[$stu->id]);
                $maxSem = (int)($highestSemMap[$stu->id] ?? $fromSemester);
                $isMarkedProgressed = isset($fromSemProgressedMap[$stu->id]);

                $classTypeName = $stu->classType ? $stu->classType->name : '';
                $classSemNo = 0;
                if (preg_match('/(\d+)\s*(st|nd|rd|th)?\s*Sem/i', $classTypeName, $m)) {
                    $classSemNo = (int)$m[1];
                } elseif (preg_match('/Sem(?:ester)?\s*(\d+)/i', $classTypeName, $m)) {
                    $classSemNo = (int)$m[1];
                }

                $isUpgraded = ($hasTargetSem || $maxSem >= $toSemester || $isMarkedProgressed || ($classSemNo > 0 && $classSemNo >= $toSemester));
                $actualCurrentSem = $isUpgraded ? max($toSemester, $maxSem, $classSemNo) : $fromSemester;

                $summary['total']++;
                if ($isDeactive) {
                    $summary['deactive_total']++;
                } else {
                    $summary['active_total']++;
                }

                if ($isUpgraded) {
                    $summary['upgraded']++;
                } else {
                    $summary['pending']++;
                    if ($balance > 0) {
                        $summary['dues']++;
                        $summary['total_dues'] += $balance;
                    } elseif ($balance < 0) {
                        $summary['advance']++;
                        $summary['total_advance'] += abs($balance);
                    } else {
                        $summary['cleared']++;
                    }
                }

                $students->push((object)[
                    'id' => $stu->id,
                    'admissionNo' => $stu->admissionNo ?: ($stu->userName ?: ('BC' . substr($stu->batch ?: date('Y'), -2) . '-' . str_pad($stu->id, 3, '0', STR_PAD_LEFT))),
                    'name' => trim("{$stu->first_name} {$stu->last_name}") ?: ($stu->userName ?: 'Student'),
                    'father_name' => $stu->father_name ?: '',
                    'mobile' => $stu->mobile ?: '',
                    'gender' => in_array((int)$stu->gender_id, [2, 3]) ? 'Girl' : 'Boy',
                    'gender_id' => (int)$stu->gender_id,
                    'batch' => $stu->batch ?: date('Y'),
                    'status' => (int)$stu->status,
                    'is_deactive' => $isDeactive,
                    'net' => $net,
                    'paid' => $paid,
                    'balance' => $balance,
                    'is_upgraded' => $isUpgraded,
                    'actual_current_sem' => $actualCurrentSem,
                    'class_name' => $classTypeName,
                ]);
            }

            // Target Semester Standard Fee Structures (strictly Tuition Fee for progression)
            $targetStructures = CollegeFeeStructure::where('branch_id', $branchId)
                ->where('course_id', $courseId)
                ->where('semester_no', $toSemester)
                ->where(function($q) {
                    $q->where('head_name', 'LIKE', '%Tuition%')
                      ->orWhere('head_name', 'LIKE', '%Tution%');
                })
                ->when(!empty($batch), function($q) use ($batch) {
                    $q->where(function($sub) use ($batch) {
                        $sub->where('batch', $batch)->orWhereNull('batch');
                    });
                })
                ->get();

            // Fallback to fromSemester Tuition Fee only
            if ($targetStructures->isEmpty()) {
                $targetStructures = CollegeFeeStructure::where('branch_id', $branchId)
                    ->where('course_id', $courseId)
                    ->where('semester_no', $fromSemester)
                    ->where(function($q) {
                        $q->where('head_name', 'LIKE', '%Tuition%')
                          ->orWhere('head_name', 'LIKE', '%Tution%');
                    })
                    ->when(!empty($batch), function($q) use ($batch) {
                        $q->where(function($sub) use ($batch) {
                            $sub->where('batch', $batch)->orWhereNull('batch');
                        });
                    })
                    ->get();

                if ($targetStructures->isEmpty()) {
                    $distinctTuition = CollegeFeeAssignment::where('branch_id', $branchId)
                        ->where('course_id', $courseId)
                        ->where('semester_no', $fromSemester)
                        ->where(function($q) {
                            $q->where('head_name', 'LIKE', '%Tuition%')
                              ->orWhere('head_name', 'LIKE', '%Tution%');
                        })
                        ->selectRaw('head_name, MAX(gross_amount) as amount_boys, MAX(gross_amount) as amount_girls')
                        ->groupBy('head_name')
                        ->get();
                    if ($distinctTuition->isNotEmpty()) {
                        $targetStructures = $distinctTuition;
                    }
                }
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'course_id' => $courseId,
                'batch' => $batch,
                'from_semester' => $fromSemester,
                'to_semester' => $toSemester,
                'detected_current_sem' => $detectedSem ?? $fromSemester,
                'detected_target_sem' => $toSemester,
                'students' => $students,
                'targetStructures' => $targetStructures,
                'summary' => $summary,
            ]);
        }

        return view('college_fees.progression', compact(
            'courses',
            'courseId',
            'fromSemester',
            'toSemester',
            'batch',
            'students',
            'targetStructures',
            'summary',
            'maxSemesters'
        ));
    }

    /**
     * Execute Progression for Selected Students
     * Note: Progression strictly carries only 2 heads into next semester:
     * 1. Tuition Fee (continuing each student's negotiated/discounted Sem 1 fee)
     * 2. Previous Dues (carryover outstanding or negative advance credit)
     */
    public function executeProgression(Request $request)
    {
        @ini_set('max_execution_time', '300');
        @set_time_limit(300);

        $branchId = $this->getBranchId();

        $request->validate([
            'course_id' => 'required|integer',
            'from_semester' => 'required|integer',
            'to_semester' => 'required|integer',
            'student_ids' => 'required|array|min:1',
        ]);

        $courseId = (int)$request->course_id;
        $fromSemester = (int)$request->from_semester;
        $toSemester = (int)$request->to_semester;
        $studentIds = (array)$request->student_ids;
        $batch = $request->get('batch');

        // Find Target ClassType for this branch & course & semester
        $targetClass = ClassType::where('branch_id', $branchId)
            ->where('course_id', $courseId)
            ->where(function($q) use ($toSemester) {
                $q->where('name', 'LIKE', "%{$toSemester}nd Sem%")
                  ->orWhere('name', 'LIKE', "%{$toSemester}rd Sem%")
                  ->orWhere('name', 'LIKE', "%{$toSemester}th Sem%")
                  ->orWhere('name', 'LIKE', "%{$toSemester}st Sem%")
                  ->orWhere('name', 'LIKE', "%Sem {$toSemester}%")
                  ->orWhere('name', 'LIKE', "%Semester {$toSemester}%");
            })
            ->first();

        // Target Tuition fee structure in current branch
        $targetStructures = CollegeFeeStructure::where('branch_id', $branchId)
            ->where('course_id', $courseId)
            ->where('semester_no', $toSemester)
            ->where(function($q) {
                $q->where('head_name', 'LIKE', '%Tuition%')
                  ->orWhere('head_name', 'LIKE', '%Tution%');
            })
            ->when(!empty($batch), function($q) use ($batch) {
                $q->where(function($sub) use ($batch) {
                    $sub->where('batch', $batch)->orWhereNull('batch');
                });
            })
            ->get();

        // Auto-seed Tuition Fee only if not configured
        if ($targetStructures->isEmpty()) {
            $fromTuition = CollegeFeeStructure::where('branch_id', $branchId)
                ->where('course_id', $courseId)
                ->where('semester_no', $fromSemester)
                ->where(function($q) {
                    $q->where('head_name', 'LIKE', '%Tuition%')
                      ->orWhere('head_name', 'LIKE', '%Tution%');
                })
                ->when(!empty($batch), function($q) use ($batch) {
                    $q->where(function($sub) use ($batch) {
                        $sub->where('batch', $batch)->orWhereNull('batch');
                    });
                })
                ->first();

            if ($fromTuition) {
                $ns = $fromTuition->replicate();
                $ns->semester_no = $toSemester;
                $ns->save();
            } else {
                $maxTuition = CollegeFeeAssignment::where('branch_id', $branchId)
                    ->where('course_id', $courseId)
                    ->where('semester_no', $fromSemester)
                    ->where(function($q) {
                        $q->where('head_name', 'LIKE', '%Tuition%')
                          ->orWhere('head_name', 'LIKE', '%Tution%');
                    })
                    ->selectRaw('MAX(gross_amount) as max_amt')
                    ->value('max_amt') ?: 4000;

                $ns = new CollegeFeeStructure();
                $ns->branch_id = $branchId;
                $ns->course_id = $courseId;
                $ns->semester_no = $toSemester;
                $ns->batch = $batch ?: null;
                $ns->head_name = 'Tuition Fee';
                $ns->amount_boys = (float)$maxTuition;
                $ns->amount_girls = (float)$maxTuition;
                $ns->save();
            }

            $targetStructures = CollegeFeeStructure::where('branch_id', $branchId)
                ->where('course_id', $courseId)
                ->where('semester_no', $toSemester)
                ->where(function($q) {
                    $q->where('head_name', 'LIKE', '%Tuition%')
                      ->orWhere('head_name', 'LIKE', '%Tution%');
                })
                ->get();
        }

        $matchingTargetTuition = $targetStructures->first();
        $targetTuitionHeadName = $matchingTargetTuition ? $matchingTargetTuition->head_name : 'Tuition Fee';
        $targetTuitionStructId = $matchingTargetTuition ? $matchingTargetTuition->id : null;
        $defaultBoysAmt = $matchingTargetTuition ? (float)$matchingTargetTuition->amount_boys : 4000;
        $defaultGirlsAmt = $matchingTargetTuition ? (float)$matchingTargetTuition->amount_girls : 3500;

        // HIGH-PERFORMANCE BULK LOOKUPS (0 round-trip queries in loop)
        // 1. Fetch all active students in ONE query
        $activeStudents = Admission::where('branch_id', $branchId)
            ->whereIn('id', $studentIds)
            ->where('status', 1)
            ->get(['id', 'gender_id', 'status']);

        $activeStudentIds = $activeStudents->pluck('id')->toArray();
        if (empty($activeStudentIds)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No active students found to progress (deactivated students are excluded).'
                ], 422);
            }
            return redirect()->back()->with('error', 'No active students found to progress.');
        }

        // 2. Bulk lookup previous balances in ONE query
        $prevNetMap = CollegeFeeAssignment::where('branch_id', $branchId)
            ->whereIn('student_id', $activeStudentIds)
            ->where('semester_no', $fromSemester)
            ->groupBy('student_id')
            ->selectRaw('student_id, SUM(net_amount) as total_net')
            ->pluck('total_net', 'student_id')
            ->toArray();

        $prevPaidMap = CollegeFeeReceipt::where('branch_id', $branchId)
            ->whereIn('student_id', $activeStudentIds)
            ->where('semester_no', $fromSemester)
            ->groupBy('student_id')
            ->selectRaw('student_id, SUM(amount_paid) as total_paid')
            ->pluck('total_paid', 'student_id')
            ->toArray();

        // 3. Bulk lookup existing target semester assignments in ONE query
        $existingAssignmentsMap = CollegeFeeAssignment::where('branch_id', $branchId)
            ->whereIn('student_id', $activeStudentIds)
            ->where('semester_no', $toSemester)
            ->get(['student_id', 'head_name', 'is_carried_dues'])
            ->groupBy('student_id');

        // 4. Bulk lookup student previous tuition assignments in ONE query
        $prevTuitionsMap = CollegeFeeAssignment::where('branch_id', $branchId)
            ->whereIn('student_id', $activeStudentIds)
            ->where('semester_no', $fromSemester)
            ->where(function($q) {
                $q->where('head_name', 'LIKE', '%Tuition%')
                  ->orWhere('head_name', 'LIKE', '%Tution%');
            })
            ->get()
            ->keyBy('student_id');

        $now = now()->toDateTimeString();
        $creatorId = Session::get('id') ?: 1;
        $newAssignmentsToInsert = [];
        $promotedCount = 0;

        foreach ($activeStudents as $student) {
            $stuId = $student->id;
            $isGirl = in_array((int)$student->gender_id, [2, 3]);

            // 1. Balance from previous semester
            $prevNet = (float)($prevNetMap[$stuId] ?? 0);
            $prevPaid = (float)($prevPaidMap[$stuId] ?? 0);
            $carryBalance = round($prevNet - $prevPaid, 2);

            $assignedList = $existingAssignmentsMap[$stuId] ?? collect();

            // 2. User requirement: ONLY TUITION FEE and PREVIOUS DUES are carried forward!
            // RULE: Previous semester balance (whether positive dues or negative advance credit) goes to Previous Dues!
            // Tuition Fee in the new semester starts clean with paid_amount = 0.
            $tuitionAlreadyAssigned = $assignedList->first(function($a) {
                $n = strtolower($a->head_name);
                return strpos($n, 'tuit') !== false || strpos($n, 'tuti') !== false;
            });

            if (!$tuitionAlreadyAssigned) {
                $prevTuition = $prevTuitionsMap->get($stuId);
                if ($prevTuition) {
                    $grossAmt = (float)$prevTuition->gross_amount;
                    $discAmt = (float)$prevTuition->discount_amount;
                    $discReason = $prevTuition->discount_reason;
                    $netAmt = (float)$prevTuition->net_amount;

                    if ($netAmt <= 0 && $grossAmt > 0) {
                        $netAmt = $grossAmt - $discAmt;
                    }

                    $newPaidAmt = 0;
                    $newDueAmt = $netAmt;
                    $newStatus = ($newDueAmt <= 0) ? 'Paid' : 'Unpaid';

                    $newAssignmentsToInsert[] = [
                        'branch_id' => $branchId,
                        'student_id' => $stuId,
                        'course_id' => $courseId,
                        'semester_no' => $toSemester,
                        'structure_id' => $targetTuitionStructId ?: $prevTuition->structure_id,
                        'head_name' => $targetTuitionHeadName,
                        'gross_amount' => $grossAmt,
                        'discount_amount' => $discAmt,
                        'discount_reason' => $discReason,
                        'net_amount' => $netAmt,
                        'paid_amount' => $newPaidAmt,
                        'due_amount' => $newDueAmt,
                        'is_carried_dues' => 0,
                        'status' => $newStatus,
                        'created_by' => $creatorId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                } elseif ($matchingTargetTuition) {
                    $amt = $isGirl ? $defaultGirlsAmt : $defaultBoysAmt;
                    if ($amt <= 0 && $defaultBoysAmt > 0) $amt = $defaultBoysAmt;

                    $newPaidAmt = 0;
                    $newDueAmt = $amt;
                    $newStatus = ($newDueAmt <= 0) ? 'Paid' : 'Unpaid';

                    $newAssignmentsToInsert[] = [
                        'branch_id' => $branchId,
                        'student_id' => $stuId,
                        'course_id' => $courseId,
                        'semester_no' => $toSemester,
                        'structure_id' => $targetTuitionStructId,
                        'head_name' => $targetTuitionHeadName,
                        'gross_amount' => $amt,
                        'discount_amount' => 0,
                        'discount_reason' => null,
                        'net_amount' => $amt,
                        'paid_amount' => $newPaidAmt,
                        'due_amount' => $newDueAmt,
                        'is_carried_dues' => 0,
                        'status' => $newStatus,
                        'created_by' => $creatorId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            // 3. Carried Dues handling: Any non-zero balance (both positive dues AND negative advance credit) becomes Previous Dues!
            if ($carryBalance != 0) {
                $dueExists = $assignedList->where('is_carried_dues', 1)->isNotEmpty()
                    || $assignedList->filter(function($a) { return stripos($a->head_name, 'previous dues') !== false; })->isNotEmpty();

                if (!$dueExists) {
                    $newAssignmentsToInsert[] = [
                        'branch_id' => $branchId,
                        'student_id' => $stuId,
                        'course_id' => $courseId,
                        'semester_no' => $toSemester,
                        'structure_id' => null,
                        'head_name' => "Previous Dues (Sem {$fromSemester})",
                        'gross_amount' => $carryBalance,
                        'discount_amount' => 0,
                        'discount_reason' => null,
                        'net_amount' => $carryBalance,
                        'paid_amount' => 0,
                        'due_amount' => $carryBalance,
                        'is_carried_dues' => 1,
                        'status' => ($carryBalance < 0) ? 'Paid' : 'Unpaid',
                        'created_by' => $creatorId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                } else {
                    $existingDue = CollegeFeeAssignment::where('branch_id', $branchId)
                        ->where('student_id', $stuId)
                        ->where('semester_no', $toSemester)
                        ->where(function($q) {
                            $q->where('is_carried_dues', 1)
                              ->orWhere('head_name', 'LIKE', '%Previous Dues%');
                        })
                        ->first();
                    if ($existingDue && $existingDue->paid_amount == 0) {
                        $existingDue->gross_amount = $carryBalance;
                        $existingDue->net_amount = $carryBalance;
                        $existingDue->due_amount = $carryBalance;
                        $existingDue->status = ($carryBalance < 0) ? 'Paid' : 'Unpaid';
                        $existingDue->save();
                    }
                }
            }

            $promotedCount++;
        }

        DB::beginTransaction();
        try {
            // Bulk insert all new assignments in chunks of 200
            if (!empty($newAssignmentsToInsert)) {
                foreach (array_chunk($newAssignmentsToInsert, 200) as $chunk) {
                    CollegeFeeAssignment::insert($chunk);
                }
            }

            // Bulk update student class_type_id in ONE query
            if ($targetClass && !empty($activeStudentIds)) {
                Admission::where('branch_id', $branchId)
                    ->whereIn('id', $activeStudentIds)
                    ->update(['class_type_id' => $targetClass->id]);
            }

            // Bulk update previous semester assignments to Progressed (due_amount = 0) in ONE query
            if (!empty($activeStudentIds)) {
                CollegeFeeAssignment::where('branch_id', $branchId)
                    ->whereIn('student_id', $activeStudentIds)
                    ->where('semester_no', $fromSemester)
                    ->update([
                        'due_amount' => 0,
                        'status' => 'Progressed'
                    ]);
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Successfully progressed {$promotedCount} active students to Semester {$toSemester} in high-speed batch mode!",
                    'count' => $promotedCount
                ]);
            }

            return redirect()->back()->with('message', "Successfully progressed {$promotedCount} active students to Semester {$toSemester} in high-speed batch mode!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error executing progression: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Progression failed: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Progression failed: ' . $e->getMessage());
        }
    }

    /**
     * 7. Reports & Defaulters
     */
    public function reports(Request $request)
    {
        $branchId = $this->getBranchId();

        $courses = Course::where('branch_id', $branchId)->select('id', 'name')->orderBy('name', 'ASC')->get();
        if ($courses->isEmpty()) {
            $courses = Course::select('id', 'name')->orderBy('name', 'ASC')->get();
        }

        $batches = Admission::where('branch_id', $branchId)
            ->whereNotNull('batch')
            ->where('batch', '!=', '')
            ->distinct()
            ->orderBy('batch', 'DESC')
            ->pluck('batch')
            ->toArray();
        if (empty($batches)) {
            $batches = ['2026', '2025', '2024', '2023', '2022', '2021'];
        }

        $financialYears = DB::table('sessions')
            ->select('id', 'from_year', 'to_year')
            ->orderBy('id', 'DESC')
            ->get()
            ->map(function($s) {
                return (object)[
                    'id' => $s->id,
                    'year' => $s->from_year . '-' . $s->to_year
                ];
            });

        $dynamicSession = helper::resolveCurrentFinancialSession($branchId);
        $currentSessionId = $dynamicSession ? $dynamicSession->id : ($financialYears->first()->id ?? null);

        $cashiers = User::where('branch_id', $branchId)->select('id', 'first_name', 'last_name')->orderBy('first_name', 'ASC')->get();

        $sessionId = $request->get('session_id');
        $fromDate = $request->has('from_date') ? $request->get('from_date') : date('Y-m-01');
        $toDate = $request->has('to_date') ? $request->get('to_date') : date('Y-m-d');
        $courseId = $request->get('course_id');
        $batch = $request->get('batch');
        $semesterNo = $request->get('semester_no');
        $paymentMode = $request->get('payment_mode');
        $cashierId = $request->get('cashier_id');
        $minDues = (float)$request->get('min_dues', 1);
        $receiptStatus = $request->get('receipt_status', 'all'); // 'all', 'paid', 'cancelled'
        $activeTab = $request->get('tab', 'register');

        // Batch optimization: Pre-fetch student IDs for fast indexed lookup (avoids slow correlated whereHas subquery)
        $batchStudentIds = null;
        if (!empty($batch)) {
            $batchStudentIds = Admission::where('branch_id', $branchId)
                ->where('batch', $batch)
                ->pluck('id')
                ->toArray();
        }

        // Receipts query (Comprehensive serial listing including Paid & Cancelled)
        $receiptsQuery = CollegeFeeReceipt::select([
            'id', 'receipt_no', 'branch_id', 'student_id', 'course_id',
            'semester_no', 'session_id', 'amount_paid', 'payment_mode',
            'payment_date', 'transaction_id', 'bank_name', 'cashier_id',
            'deleted_at'
        ])
        ->with([
            'student:id,admissionNo,first_name,last_name,father_name,batch,mobile',
            'course:id,name',
            'cashier:id,first_name,last_name'
        ])
        ->where('branch_id', $branchId);

        // Status Filter: all (withTrashed), paid (active only), cancelled (onlyTrashed)
        if ($receiptStatus === 'cancelled') {
            $receiptsQuery->onlyTrashed();
        } elseif ($receiptStatus === 'paid') {
            // Default active non-cancelled receipts
        } else {
            // 'all': Include both active and cancelled so no receipt serial number is missed!
            $receiptsQuery->withTrashed();
        }

        if (!empty($sessionId)) {
            $receiptsQuery->where('session_id', $sessionId);
        }

        if (!empty($fromDate) && !empty($toDate)) {
            $receiptsQuery->whereBetween('payment_date', [$fromDate, $toDate]);
        } elseif (!empty($fromDate)) {
            $receiptsQuery->where('payment_date', '>=', $fromDate);
        } elseif (!empty($toDate)) {
            $receiptsQuery->where('payment_date', '<=', $toDate);
        }

        if (!empty($courseId)) {
            $receiptsQuery->where('course_id', $courseId);
        }
        if ($batchStudentIds !== null) {
            $receiptsQuery->whereIn('student_id', $batchStudentIds);
        }
        if (!empty($semesterNo)) {
            $receiptsQuery->where('semester_no', $semesterNo);
        }
        if (!empty($paymentMode)) {
            $receiptsQuery->where('payment_mode', $paymentMode);
        }
        if (!empty($cashierId)) {
            $receiptsQuery->where('cashier_id', $cashierId);
        }

        $receipts = $receiptsQuery->orderBy('payment_date', 'DESC')->orderBy('id', 'DESC')->get();
        // Total collected reflects active paid receipts (cancelled amount was reverted)
        $totalCollected = (float)$receipts->whereNull('deleted_at')->sum('amount_paid');
        $totalCancelled = (float)$receipts->whereNotNull('deleted_at')->sum('amount_paid');
        $paidCount = $receipts->whereNull('deleted_at')->count();
        $cancelledCount = $receipts->whereNotNull('deleted_at')->count();

        // Defaulters query (Fast grouped query on indexed assignment records)
        $defaultersQuery = CollegeFeeAssignment::with([
            'student:id,admissionNo,first_name,last_name,father_name,batch,mobile',
            'course:id,name'
        ])
        ->where('branch_id', $branchId)
        ->where('due_amount', '>', 0);

        if (!empty($courseId)) {
            $defaultersQuery->where('course_id', $courseId);
        }
        if ($batchStudentIds !== null) {
            $defaultersQuery->whereIn('student_id', $batchStudentIds);
        }
        if (!empty($semesterNo)) {
            $defaultersQuery->where('semester_no', $semesterNo);
        }

        $defaulters = $defaultersQuery
            ->select(
                'student_id',
                'course_id',
                DB::raw('SUM(gross_amount) as total_demand'),
                DB::raw('SUM(paid_amount) as total_paid'),
                DB::raw('SUM(due_amount) as total_due'),
                DB::raw('MAX(semester_no) as current_sem')
            )
            ->groupBy('student_id', 'course_id')
            ->having('total_due', '>=', $minDues)
            ->orderBy('total_due', 'DESC')
            ->get();

        $totalDefaultersDues = (float)$defaulters->sum('total_due');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'receipts' => $receipts->map(function($r) {
                    $isCancelled = !is_null($r->deleted_at);
                    return [
                        'id' => $r->id,
                        'receipt_no' => $r->receipt_no,
                        'payment_date' => date('d-M-Y', strtotime($r->payment_date)),
                        'student_name' => trim(($r->student->first_name ?? '') . ' ' . ($r->student->last_name ?? '')),
                        'father_name' => $r->student->father_name ?? '',
                        'admissionNo' => $r->student->admissionNo ?? '',
                        'batch' => $r->student->batch ?? '',
                        'mobile' => $r->student->mobile ?? '',
                        'course_name' => $r->course->name ?? 'Course',
                        'semester_no' => $r->semester_no,
                        'payment_mode' => $r->payment_mode,
                        'transaction_id' => $r->transaction_id ?? '',
                        'bank_name' => $r->bank_name ?? '',
                        'amount_paid' => (float)$r->amount_paid,
                        'cashier_name' => trim(($r->cashier->first_name ?? '') . ' ' . ($r->cashier->last_name ?? '')) ?: 'System / Admin',
                        'receipt_url' => route('college.fees.receipt', $r->id),
                        'is_cancelled' => $isCancelled,
                        'status' => $isCancelled ? 'cancelled' : 'paid',
                        'status_label' => $isCancelled ? 'Cancelled' : 'Paid'
                    ];
                }),
                'total_collected' => $totalCollected,
                'total_cancelled' => $totalCancelled,
                'paid_count' => $paidCount,
                'cancelled_count' => $cancelledCount,
                'receipts_count' => count($receipts),
                'defaulters' => $defaulters->map(function($def) {
                    return [
                        'student_id' => $def->student_id,
                        'admissionNo' => $def->student->admissionNo ?? 'N/A',
                        'student_name' => trim(($def->student->first_name ?? '') . ' ' . ($def->student->last_name ?? '')),
                        'father_name' => $def->student->father_name ?? 'N/A',
                        'batch' => $def->student->batch ?? '',
                        'course_name' => $def->course->name ?? 'Course',
                        'current_sem' => $def->current_sem,
                        'mobile' => $def->student->mobile ?? '',
                        'total_demand' => (float)$def->total_demand,
                        'total_paid' => (float)$def->total_paid,
                        'total_due' => (float)$def->total_due,
                        'collect_url' => route('college.fees.structures'), 'terminal_url' => route('college.fees.structures')
                    ];
                }),
                'defaulters_count' => count($defaulters),
                'total_defaulters_dues' => $totalDefaultersDues
            ]);
        }

        $branch = \App\Models\Master\Branch::find($branchId);

        return view('college_fees.reports', compact(
            'courses',
            'batches',
            'financialYears',
            'currentSessionId',
            'sessionId',
            'cashiers',
            'fromDate',
            'toDate',
            'courseId',
            'batch',
            'semesterNo',
            'paymentMode',
            'cashierId',
            'receiptStatus',
            'activeTab',
            'receipts',
            'totalCollected',
            'totalCancelled',
            'paidCount',
            'cancelledCount',
            'defaulters',
            'totalDefaultersDues',
            'minDues',
            'branch',
            'dynamicSession'
        ));
    }

    /**
     * Number to Words helper (Indian format)
     */
    protected function numberToWords($num)
    {
        $ones = [
            0 => "Zero", 1 => "One", 2 => "Two", 3 => "Three", 4 => "Four", 5 => "Five",
            6 => "Six", 7 => "Seven", 8 => "Eight", 9 => "Nine", 10 => "Ten",
            11 => "Eleven", 12 => "Twelve", 13 => "Thirteen", 14 => "Fourteen", 15 => "Fifteen",
            16 => "Sixteen", 17 => "Seventeen", 18 => "Eighteen", 19 => "Nineteen"
        ];
        $tens = [
            2 => "Twenty", 3 => "Thirty", 4 => "Forty", 5 => "Fifty",
            6 => "Sixty", 7 => "Seventy", 8 => "Eighty", 9 => "Ninety"
        ];

        if ($num < 20) return $ones[$num];
        if ($num < 100) return $tens[(int)($num / 10)] . (($num % 10 != 0) ? " " . $ones[$num % 10] : "");
        if ($num < 1000) return $ones[(int)($num / 100)] . " Hundred" . (($num % 100 != 0) ? " and " . $this->numberToWords($num % 100) : "");
        if ($num < 100000) return $this->numberToWords((int)($num / 1000)) . " Thousand" . (($num % 1000 != 0) ? " " . $this->numberToWords($num % 1000) : "");
        if ($num < 10000000) return $this->numberToWords((int)($num / 100000)) . " Lakh" . (($num % 100000 != 0) ? " " . $this->numberToWords($num % 100000) : "");
        return $this->numberToWords((int)($num / 10000000)) . " Crore" . (($num % 10000000 != 0) ? " " . $this->numberToWords($num % 10000000) : "");
    }
/**
     * View: Consolidated Course Fees Ledger (Multi-Semester Grid & Excel Export)
     */
    public function courseLedger(Request $request)
    {
        $branchId = $this->getBranchId();

        $courses = Course::where('branch_id', $branchId)->orderBy('name', 'ASC')->get();
        if ($courses->isEmpty()) {
            $courses = Course::orderBy('name', 'ASC')->get();
        }

        $batches = Admission::where('branch_id', $branchId)
            ->whereNotNull('batch')
            ->where('batch', '!=', '')
            ->distinct()
            ->orderBy('batch', 'DESC')
            ->pluck('batch')
            ->toArray();

        if (empty($batches)) {
            $batches = ['2026', '2025', '2024', '2023', '2022', '2021'];
        }

        $courseId = $request->get('course_id') ? (int)$request->get('course_id') : ($courses->first() ? $courses->first()->id : null);
        $batch = $request->get('batch') ? (string)$request->get('batch') : (!empty($batches) ? $batches[0] : '2025');

        return view('college_fees.course_ledger', compact('courses', 'batches', 'courseId', 'batch'));
    }

    /**
     * AJAX: Get Consolidated Multi-Semester Course Fee Ledger Data
     */
    public function getCourseLedgerData(Request $request)
    {
        $branchId = $this->getBranchId();
        $courseId = $request->get('course_id') ? (int)$request->get('course_id') : null;
        $batch = $request->get('batch') ? (string)$request->get('batch') : null;
        $statusFilter = strtolower($request->get('status', 'all'));
        $genderFilter = strtolower($request->get('gender', 'all'));
        $search = trim($request->get('search', ''));

        if (empty($courseId) || empty($batch)) {
            return response()->json([
                'status' => 'empty',
                'message' => 'Please select a Course and Batch to view the consolidated fee ledger.',
                'course' => null,
                'batch' => $batch,
                'kpi' => [
                    'total_enrolled' => 0,
                    'filtered_count' => 0,
                    'total_net' => 0,
                    'total_paid' => 0,
                    'total_due' => 0,
                    'collection_rate' => 0,
                    'paid_students' => 0,
                    'partial_students' => 0,
                    'unpaid_students' => 0,
                ],
                'semesters' => [],
                'rows' => [],
                'grand_totals' => []
            ]);
        }

        $course = Course::find($courseId);
        if (!$course) {
            return response()->json(['status' => 'error', 'message' => 'Selected course not found.']);
        }

        $totalSemesters = (int)($course->total_semester ?: ($course->duration ? $course->duration * 2 : 4));
        if ($totalSemesters <= 0) $totalSemesters = 4;
        $courseName = $course->name;

        // Fetch students (optimized query)
        $classTypeIds = ClassType::where('course_id', $courseId)->pluck('id')->toArray();
        $studentsQuery = Admission::where('branch_id', $branchId)
            ->where('status', 1)
            ->where('school', 1)
            ->where(function($q) use ($courseId, $courseName, $classTypeIds) {
                $q->where('course', $courseName);
                if (!empty($classTypeIds)) {
                    $q->orWhereIn('class_type_id', $classTypeIds);
                }
            })
            ->when(!empty($batch), function($q) use ($batch) {
                $q->where('batch', $batch);
            })
            ->orderBy('first_name', 'ASC');

        $allStudents = $studentsQuery->select('id', 'first_name', 'last_name', 'father_name', 'mobile', 'admissionNo', 'userName', 'gender_id')->get();
        $totalEnrolled = $allStudents->count();

        if ($allStudents->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'course' => [
                    'id' => $course->id,
                    'name' => $course->name,
                    'duration' => $course->duration,
                    'total_semesters' => $totalSemesters
                ],
                'batch' => $batch,
                'kpi' => [
                    'total_enrolled' => 0,
                    'filtered_count' => 0,
                    'total_net' => 0,
                    'total_paid' => 0,
                    'total_due' => 0,
                    'collection_rate' => 0,
                    'paid_students' => 0,
                    'partial_students' => 0,
                    'unpaid_students' => 0,
                ],
                'semesters' => range(1, $totalSemesters),
                'rows' => [],
                'grand_totals' => []
            ]);
        }

        $studentIds = $allStudents->pluck('id')->toArray();

        // Fetch all assignments across all semesters for this course (direct DB query, fast & respecting soft deletes)
        $rawAssignments = DB::table('college_fee_assignments')
            ->where('branch_id', $branchId)
            ->whereIn('student_id', $studentIds)
            ->whereNull('deleted_at')
            ->select('id', 'student_id', 'semester_no', 'head_name', 'gross_amount', 'discount_amount', 'net_amount', 'paid_amount', 'due_amount', 'is_carried_dues')
            ->get();

        $assignmentsMap = [];
        foreach ($rawAssignments as $as) {
            $assignmentsMap[$as->student_id][] = $as;
        }

        $rows = [];
        $kpi = [
            'total_enrolled' => $totalEnrolled,
            'filtered_count' => 0,
            'total_net' => 0.0,
            'total_paid' => 0.0,
            'total_due' => 0.0,
            'collection_rate' => 0.0,
            'paid_students' => 0,
            'partial_students' => 0,
            'unpaid_students' => 0,
        ];

        $grandTotals = [
            'total_gross' => 0.0,
            'total_discount' => 0.0,
            'total_net' => 0.0,
            'total_paid' => 0.0,
            'total_due' => 0.0,
            'sem_totals' => [],
            'prev_dues_total' => ['gross' => 0.0, 'net' => 0.0, 'paid' => 0.0, 'due' => 0.0]
        ];

        for ($s = 1; $s <= $totalSemesters; $s++) {
            $grandTotals['sem_totals'][$s] = [
                'gross' => 0.0,
                'discount' => 0.0,
                'net' => 0.0,
                'paid' => 0.0,
                'due' => 0.0
            ];
        }

        foreach ($allStudents as $stu) {
            $sId = $stu->id;
            $stuAssigns = $assignmentsMap[$sId] ?? [];

            $semData = [];
            for ($s = 1; $s <= $totalSemesters; $s++) {
                $semData[$s] = [
                    'gross' => 0.0,
                    'discount' => 0.0,
                    'net' => 0.0,
                    'paid' => 0.0,
                    'due' => 0.0,
                    'heads' => []
                ];
            }

            $prevDuesData = [
                'gross' => 0.0,
                'discount' => 0.0,
                'net' => 0.0,
                'paid' => 0.0,
                'due' => 0.0
            ];

            $stuGross = 0.0;
            $stuDisc = 0.0;
            $stuNet = 0.0;
            $stuPaid = 0.0;
            $stuDue = 0.0;

            foreach ($stuAssigns as $as) {
                $sNo = (int)$as->semester_no;
                $isPrev = ((int)$as->is_carried_dues === 1 || strtolower(trim($as->head_name)) === 'previous dues');

                $g = (float)$as->gross_amount;
                $d = (float)$as->discount_amount;
                $n = (float)$as->net_amount;
                $p = (float)$as->paid_amount;
                $du = (float)$as->due_amount;

                if ($isPrev) {
                    $prevDuesData['gross'] += $g;
                    $prevDuesData['net'] += $n;
                    $prevDuesData['paid'] += $p;
                    $prevDuesData['due'] += $du;
                } else {
                    if (isset($semData[$sNo])) {
                        $semData[$sNo]['gross'] += $g;
                        $semData[$sNo]['discount'] += $d;
                        $semData[$sNo]['net'] += $n;
                        $semData[$sNo]['paid'] += $p;
                        $semData[$sNo]['due'] += $du;
                        $semData[$sNo]['heads'][] = [
                            'name' => $as->head_name,
                            'net' => $n,
                            'paid' => $p,
                            'due' => $du
                        ];
                    }
                }

                $stuGross += $g;
                $stuDisc += $d;
                $stuNet += $n;
                $stuPaid += $p;
                $stuDue += $du;
            }

            $overallStatus = ($stuDue <= 0 && $stuNet > 0) ? 'Paid' : (($stuPaid > 0 && $stuDue > 0) ? 'Partial' : ($stuNet > 0 ? 'Unpaid' : ($stuDue <= 0 ? 'Paid' : 'Unpaid')));
            if ($stuDue <= 0 && $stuPaid <= 0 && $stuNet <= 0) {
                $overallStatus = 'Paid';
            }

            // Gender filter
            $genderStr = ((int)$stu->gender_id === 1) ? 'boys' : 'girls';
            if ($genderFilter !== 'all' && $genderFilter !== $genderStr) {
                continue;
            }

            // Status filter
            if ($statusFilter !== 'all') {
                if ($statusFilter === 'paid' && $overallStatus !== 'Paid') continue;
                if ($statusFilter === 'partial' && $overallStatus !== 'Partial') continue;
                if ($statusFilter === 'unpaid' && $overallStatus !== 'Unpaid') continue;
                if ($statusFilter === 'overdue' && ($stuDue <= 0)) continue;
            }

            // Search filter
            $fullName = trim(($stu->first_name ?: '') . ' ' . ($stu->last_name ?: ''));
            $fatherName = trim($stu->father_name ?: '');
            $admNo = trim($stu->admissionNo ?: ($stu->userName ?: ''));

            if (!empty($search)) {
                $searchLower = strtolower($search);
                $match = (
                    strpos(strtolower($fullName), $searchLower) !== false ||
                    strpos(strtolower($fatherName), $searchLower) !== false ||
                    strpos(strtolower($admNo), $searchLower) !== false
                );
                if (!$match) continue;
            }

            // Update KPI counts
            if ($overallStatus === 'Paid') {
                $kpi['paid_students']++;
            } elseif ($overallStatus === 'Partial') {
                $kpi['partial_students']++;
            } else {
                $kpi['unpaid_students']++;
            }

            $kpi['total_net'] += $stuNet;
            $kpi['total_paid'] += $stuPaid;
            $kpi['total_due'] += $stuDue;

            // Grand totals
            $grandTotals['total_gross'] += $stuGross;
            $grandTotals['total_discount'] += $stuDisc;
            $grandTotals['total_net'] += $stuNet;
            $grandTotals['total_paid'] += $stuPaid;
            $grandTotals['total_due'] += $stuDue;

            for ($s = 1; $s <= $totalSemesters; $s++) {
                $grandTotals['sem_totals'][$s]['gross'] += $semData[$s]['gross'];
                $grandTotals['sem_totals'][$s]['discount'] += $semData[$s]['discount'];
                $grandTotals['sem_totals'][$s]['net'] += $semData[$s]['net'];
                $grandTotals['sem_totals'][$s]['paid'] += $semData[$s]['paid'];
                $grandTotals['sem_totals'][$s]['due'] += $semData[$s]['due'];
            }

            $grandTotals['prev_dues_total']['gross'] += $prevDuesData['gross'];
            $grandTotals['prev_dues_total']['net'] += $prevDuesData['net'];
            $grandTotals['prev_dues_total']['paid'] += $prevDuesData['paid'];
            $grandTotals['prev_dues_total']['due'] += $prevDuesData['due'];

            $rows[] = [
                'student_id' => $sId,
                'admission_no' => $admNo,
                'student_name' => $fullName,
                'father_name' => $fatherName,
                'mobile' => $stu->mobile ?: '',
                'gender' => ((int)$stu->gender_id === 1) ? 'Male' : 'Female',
                'semesters' => $semData,
                'previous_dues' => $prevDuesData,
                'totals' => [
                    'gross' => $stuGross,
                    'discount' => $stuDisc,
                    'net' => $stuNet,
                    'paid' => $stuPaid,
                    'due' => $stuDue,
                    'status' => $overallStatus
                ]
            ];
        }

        $kpi['filtered_count'] = count($rows);
        $kpi['collection_rate'] = ($kpi['total_net'] > 0) ? round(($kpi['total_paid'] / $kpi['total_net']) * 100, 1) : 0;

        return response()->json([
            'status' => 'success',
            'course' => [
                'id' => $course->id,
                'name' => $course->name,
                'duration' => $course->duration,
                'total_semesters' => $totalSemesters
            ],
            'batch' => $batch,
            'kpi' => $kpi,
            'semesters' => range(1, $totalSemesters),
            'rows' => $rows,
            'grand_totals' => $grandTotals
        ]);
    }
}
