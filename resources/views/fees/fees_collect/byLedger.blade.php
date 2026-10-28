@php
$getstudents = Helper::getstudents();
$courses = Helper::getCourses();
$classType = Helper::classType($serach['course_id'] ?? null);
$allClassType = Helper::classType();
$getPaymentMode = Helper::getPaymentMode();
$getSession = Helper::getSession();
$batches = Helper::getBatch();
@endphp
@extends('layout.app')
@section('content')
<style>
/* ========================================================
   COLLECT BY LEDGER - EXCEL GREEN EXECUTIVE THEME (#217346)
   ======================================================== */
:root {
    --green-excel: #217346;
    --green-excel-dark: #185c37;
    --green-excel-light: #f0fdf4;
    --line: #b8c8d5;
    --line-subtle: #cbd5e1;
}

.byledger-page {
    background-color: #f4f6f9;
    font-size: 12px;
}

.dash-main-card {
    border: 1px solid var(--line) !important;
    border-top: 3px solid var(--green-excel) !important;
    border-radius: 4px !important;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06) !important;
    background: #ffffff;
    margin-bottom: 12px;
    overflow: hidden;
}

.dash-titlebar {
    min-height: 42px;
    padding: 6px 14px;
    background: var(--green-excel) !important;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.dash-title {
    margin: 0;
    font-size: 14.5px;
    font-weight: 700;
    letter-spacing: 0.2px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #ffffff !important;
}

.dash-title small {
    font-size: 11.5px;
    font-weight: 400;
    color: #e2f0d9;
    margin-left: 6px;
}

.btn-header-action {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #1e293b !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    border-radius: 3px !important;
    padding: 4px 10px !important;
    text-decoration: none !important;
    transition: all 0.15s ease-in-out;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}
.btn-header-action:hover {
    background: #f8fafc !important;
    color: #0f172a !important;
}

.filter-card {
    background: #f8fafc;
    border-bottom: 1px solid var(--line-subtle);
    padding: 10px 14px 6px 14px;
}
.form-label-compact {
    font-size: 10.5px;
    font-weight: 700;
    margin-bottom: 2px;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.35px;
    display: block;
}
.form-control-compact {
    height: 30px !important;
    font-size: 12px !important;
    padding: 3px 8px !important;
    border-radius: 3px !important;
    border: 1px solid #cbd5e1 !important;
}

.select2-container--default .select2-selection--single {
    height: 30px !important;
    padding: 2px 6px !important;
    font-size: 12px !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 3px !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 24px !important;
    padding-left: 2px !important;
}

.padding_table thead tr th {
    background: #1e293b !important;
    color: #ffffff !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    padding: 6px 8px !important;
    vertical-align: middle;
}
.padding_table td {
    padding: 5px 8px !important;
    font-size: 12px !important;
    vertical-align: middle !important;
}
.padding_table tbody tr:hover {
    background-color: #f0fdf4 !important;
}
.padding_table tbody tr.active-student {
    background-color: #217346 !important;
    color: #ffffff !important;
}
.padding_table tbody tr.active-student td,
.padding_table tbody tr.active-student a {
    color: #ffffff !important;
}
</style>

<div class="content-wrapper byledger-page">
    <section class="content pt-2">
        <div class="container-fluid px-2">
            <div class="dash-main-card">
                <div class="dash-titlebar">
                    <h1 class="dash-title">
                        <i class="fa fa-money text-warning"></i>
                        <span>{{ __('Collect Student Fees By Ledger') }}</span>
                        <small>Ledger Matrix &amp; Multi-head Student Fee Records</small>
                    </h1>
                    <div class="dash-title-tools">
                        <a href="{{ url('fees_assign') }}" class="btn-header-action" title="Assign Fees">
                            <i class="fa fa-users text-primary mr-1"></i> Fee Assign
                        </a>
                        <a href="{{ url('fee_dashboard') }}" class="btn-header-action" title="Back">
                            <i class="fa fa-arrow-left text-secondary mr-1"></i> {{ __('common.Back') }}
                        </a>
                    </div>
                </div>
                        <div class="card-body">
                            <form id="quickForm" method="post" action="{{ url('fees/ledger/collect') }}">
                                @csrf
                                <div class="row m-2">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>{{ __('Session') }}</label>
                                            <select class="form-control select2" id="session_id" name="session_id">
                                                <option value="">{{ __('common.All') }}</option>
                                                @if(!empty($getSession))
                                                    @foreach($getSession as $session)
                                                        <option value="{{ $session->id ?? '' }}" {{ ($session->id == ($serach['session_id'] ?? '')) ? 'selected' : '' }}>{{ $session->from_year ?? ''  }} - {{ $session->to_year ?? ''  }}</option>
                                                    @endforeach
                                                @endif
                                            </select>                 	    
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>{{ __('Course') }}</label>
                                            <select class="form-control select2" id="course_id" name="course_id">
                                                <option value="">{{ __('common.Select') }}</option>
                                                @if(!empty($courses))
                                                    @foreach($courses as $course)
                                                        <option value="{{ $course->id ?? '' }}" {{ ($course->id == ($serach['course_id'] ?? '')) ? 'selected' : '' }}>{{ $course->name ?? '' }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>{{ __('Class/Semester') }}</label>
                                            <select class="form-control select2" id="class_type_id" name="class_type_id">
                                                <option value="">{{ __('common.Select') }}</option>
                                                @if(!empty($classType))
                                                    @foreach($classType as $type)
                                                        <option value="{{ $type->id ?? '' }}" {{ ($type->id == ($serach['class_type_id'] ?? '')) ? 'selected' : '' }}>{{ $type->name ?? '' }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>{{ __('Batch') }}</label>
                                            <select class="form-control select2" id="batch" name="batch">
                                                <option value="">{{ __('common.Select') }}</option>
                                                @if(!empty($batches))
                                                    @foreach($batches as $batch)
                                                        <option value="{{ $batch->name ?? '' }}" {{ ($batch->name == ($serach['batch'] ?? '')) ? 'selected' : '' }}>{{ $batch->name ?? '' }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>{{ __('Ledger No') }}</label>
                                            <input type="text" class="form-control" value="{{$serach['sr_no'] ?? ''}}" id="sr_no" name="sr_no" placeholder="{{ __('Type Ledger No.') }}">
                                            @error('sr_no')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>{{ __('common.Search By Keywords') }}</label>
                                            <input type="text" class="form-control" value="{{$serach['name'] ?? ''}}" id="name" name="name" placeholder="{{ __('common.Search By Keywords') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label class="text-white">{{ __('common.Search') }}</label>
                                            <button type="submit" class="btn btn-primary" id='form_submit'>{{ __('common.Search') }}</button>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label class="text-white">{{ __('Ledger') }}</label>
                                            <a target='_blank' href='{{url("ledger_update")}}'><button type="button" class="btn btn-success">{{ __('Ledger Update') }}</button></a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                           
                           <div><button id='toggle-columns' class='btn btn-primary  m-1'>Show All Head</button></div>
                           <div class="table-responsive">
                           <table class="table table-bordered" id="trColor" style="cursor:pointer;font-weight: 500 !important;">
                                    <thead>
                                        
                                        <tr>
                                            <th>Ledger no</th>
                                            <th class="text-center">Father's/Account Holder </th>
                                            <th>Name of Student</th>
                                            <th>Class</th>
                                             <!-- <th>{{ __('Add Head') }}</th>-->
                                            
                                          @php
                                            $feesGroups = DB::table('fees_group')->where('session_id',Session::get('session_id'))->whereNull('deleted_at')->get();
                                            //dd($feesGroups);
                                            @endphp
                                           
                                           @if(!empty($feesGroups))
                                           
                                           @foreach($feesGroups as $key1=>$item)
                                          
                                           
                                            <th id="key_{{$key1}}"style="text-align: center;">{{$item->name ?? ''}}
                                            <br>  <span style="display: flex; justify-content: space-between; width: 100%; ">
                                                    <span style="width: 78px;">Total</span>
                                                    <span style="color: green; border-right: 1px solid #00000057; border-left: 1px solid #00000057;width: 78px;">Credited</span>
                                                    <span style="color: red;width: 78px;">Due</span>
                                                </span>
                                           @endforeach
                                           
                                           @endif
                                        </tr>
                                    </thead>
                                    <tbody >
                   @if(!empty($data))
                   
                     @php
                                        $i=1;
                    $admission_id = array();
                                        @endphp
                                       @foreach ($data as $key =>$item)
                                       
                                       @php
                                        $total_amount =0;
                                        $admission_id[] = $item->id;
                                       @endphp
                                            <tr class="quickCollect" style="cursor:pointer; " onclick="showData('{{ $item['id']  }}',{{ $item->ledger_no ?? ''}})" >
                                            <td>{{ $item->ledger_no ?? '' }}</td>
                                            <td class="text-center">{{ $item['father_name'] ?? '' }}</td>
                                            <td class='openTabButton' title="Add Fee Head"  data-admission_id="{{ $item['id']  }}"><a>{{ $item['first_name'] ?? '' }} {{ $item['last_name'] ?? '' }}</td>
                                            <td>{{ $item['ClassTypes']['name'] ?? '' }}</td>
<!--                                              <td> <button class='btn btn-sm btn-primary openTabButton' data-admission_id="{{ $item['id']  }}">Add</button>
-->                  </td>

                                             @if(!empty($feesGroups))
                                           
                                           @foreach($feesGroups as $item1)
                                            @php
                                           
                                            $fees_assigned = DB::table('fees_assign_details')->where('admission_id',$item->id)->where('fees_group_id',$item1->id)
                                            ->where('branch_id',Session::get('branch_id'))
                                            ->where('session_id',Session::get('session_id'))
                                            ->whereNull('deleted_at')->first();
                                       
                                           
                                           $total_amount1 = DB::table('fees_detail')
                                                            ->where('admission_id',$item->id) // Casting to an array
                                                            ->where('fees_group_id', $item1->id)
                                                            ->where('branch_id', Session::get('branch_id'))
                                                            ->where('session_id', Session::get('session_id'))
                                                            ->whereNull('deleted_at')
                                                            ->sum('total_amount');
                                            @endphp
                                            <td class='amount_{{$item->id}}'>
                                                
                                                
                                                <span style="display: flex; justify-content: space-between; width: 100%;height: 49px;text-align: center;">
                                                            <span style="width: 78px;">@if(!empty($fees_assigned))
                                                   @php
                                                   $total_amount += ($fees_assigned->fees_group_amount ?? 0)-($fees_assigned->discount ?? 0);
                                                   @endphp
                                                   {{($fees_assigned->fees_group_amount ?? 0)-($fees_assigned->discount ?? 0)}}
                                                          @else
                                                          0
                                                           @php
                                               
                                                   $total_amount += 0;
                                                   @endphp
                                                    @endif</span>
                                                    <span style="color: green; border-right: 1px solid #00000057; border-left: 1px solid #00000057;width: 78px;">
                                                        {{$total_amount1 ?? '' }} </span>
                                                    <span style="color: red;width: 78px;">{{($fees_assigned->fees_group_amount ?? 0)-($fees_assigned->discount ?? 0)-($total_amount1 ?? 0) }}</span>
                                                </span>
                                                
                                                
                                                
                                                
                                               

                                           </td>
                                            
                                           @endforeach
                                           
                                           @endif
                                           
                                        
                                          
                                        </tr>                                            
                                        @endforeach
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td colspan="" style="text-align: right;">Grand Total  
                                            @if(!empty($feesGroups))
                                           
                                           @foreach($feesGroups as $key2=> $item2)
                                            @php
                                            
                                            
                                             $fees_group_amount = DB::table('fees_assign_details')
                                                                ->whereIn('admission_id', (array) $admission_id)
                                                                ->where('fees_group_id', $item2->id)
                                                                ->where('branch_id', Session::get('branch_id'))
                                                                ->where('session_id', Session::get('session_id'))
                                                                ->whereNull('deleted_at')
                                                                ->selectRaw('SUM(fees_group_amount) as total_fees, SUM(discount) as total_discount')
                                                                ->first(); // Use `first()` to get the result as an object.

                                          
                                            
                                            $total_fees_mas = $fees_group_amount->total_fees - $fees_group_amount->total_discount ??  '0';
                                                 
                                           $Pay_fees_detail = DB::table('fees_detail')
                                                        ->whereIn('admission_id', (array) $admission_id) ->where('fees_group_id', $item2->id)
                                                        ->where('branch_id', Session::get('branch_id'))
                                                        ->where('session_id', Session::get('session_id'))
                                                        ->whereNull('deleted_at')
                                                        ->sum('total_amount');

                                                                              

                                            @endphp
                                            
                                                <td  class='child' data-isZero='{{$total_fees_mas ?? 0}}' data-key='{{$key2}}'>
                                                    
                                                    
                                                    
                                                    
                                                    
                                                    
                                                      <span style="display: flex; justify-content: space-between; width: 100%;height: 49px;text-align: center;">
                                                            <span style="width: 78px;">{{$total_fees_mas ?? 0}} </span>
                                                    <span style="color: green; border-right: 1px solid #00000057; border-left: 1px solid #00000057;width: 78px;">
                                                        {{$Pay_fees_detail ?? '' }} </span>
                                                    <span style="color: red;width: 78px;">{{($total_fees_mas ?? 0)-($Pay_fees_detail ?? 0) }}</span>
                                                </span>
                                                
                                                
                                                
                                                
                                            

                                           </td>
                                          
                                           @endforeach
                                           
                                           @endif
                                        </tr>
                   
                   @endif
                                    </tbody>
                                </table>
                           
                           @php
                           
                           @endphp
                           </div>
                           
                        </div>
                    </div>
                </div>
            </div>
            <div class="student_fees_detail"></div>
        </div>
    </section>
</div>
<!-- Modal -->
<div class="modal fade" id="toastrModal" tabindex="-1" role="dialog" aria-labelledby="toastrModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div id="toastrMessage"></div>
            </div>
        </div>
    </div>
</div>

<script>


$(document).ready(function() {
    $('#trColor tbody tr').click(function() {
        $('#trColor tbody tr').removeClass('active-student');
        $(this).addClass('active-student');
    });
});

    function showData(admission_id,ledgerNo) {
        
        var searchByKeyword = $("#name").val();
       
  
      if(searchByKeyword != '' && ledgerNo !='')
      {
        
        $("#name").val('');
        $('#sr_no').val(ledgerNo);
        $('#form_submit').trigger('click');
        
        return
      }
         var basurl = "{{ url('/') }}";
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            },
            type: 'post',
            url: basurl+'/student_fees_onclick',
            data: {
                admission_id: admission_id
            },
            // dataType: 'json',
            success: function(data) {
                if (data == 0) {
                    
                    alert('Please Assign the Fees for this Student !');
                    //window.location.href = "{{ url('feesMasterAdd') }}";
                    var url = "{{ url('admissionEdit') }}/" + admission_id;
                    var width = 1000; 
                    var height = 500; 
                    var leftPosition = (window.screen.width - width) / 2; 
                    var topPosition = (window.screen.height - height) / 2; 
                    var features = 'width=' + width + ',height=' + height + ',left=' + leftPosition + ',top=' + topPosition; 
                    // var newWindow = window.open(url, '_blank', features); 
                    // if (newWindow) {
                    //     newWindow.focus(); 
                    // } else {
                    //     alert('Your browser blocked opening the new window. Please check your browser settings.'); 
                    // }
                    
                } else {
                    $('.student_fees_detail').html(data);
                }
            }
        });
    };

    function SearchValue() {
         var basurl = "{{ url('/') }}";
        var class_type_id = $('#class_type_id :selected').val();
        var name = $('#name').val();
        if (class_type_id > 0 || name != '') {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                },
                type: 'post',
                url: basurl+'/SearchValueStd',
                data: {
                    class_type_id: class_type_id,
                    name: name
                },
                //dataType: 'json',
                success: function(data) {
                    $('.student_list_show').html(data);
                }
            });
        } else {
            alert('Please put a value in minimum one column !');
        }

    };
    
        
       
   
</script>

<script>
 $(document).ready(function(){
    var allStudents = @json($srno);
    var old_class_value = parseInt("{{$serach['class_type_id'] ?? ''}}");
    
    if(old_class_value > 0)
    {
            allStudents.forEach(function(item, index) {
            if(parseInt(item.class_type_id) == old_class_value) {        
                $('#sr_no').append('<option value="' + item.ledger_no + '">' + item.ledger_no + ' / '+ item.father_name + '</option>');
            }
        });
        
         var old_srno_value = "{{$serach['sr_no'] ?? ''}}";
         $('#sr_no').val(old_srno_value)
    }

    $("#class_type_id").on("change", function(){
        var class_type_id = $(this).val();

        $('#sr_no').empty();
        
        allStudents.forEach(function(item, index) {
            if(parseInt(item.class_type_id) == parseInt(class_type_id)) {
              $('#sr_no').append('<option value="' + item.ledger_no + '">' + item.ledger_no + ' / '+ item.father_name + '</option>');
            }
        });
    });
    
    
});
</script>
<script>
    $(document).ready(function() {
          var thisTarget ='';
        var basurl = "{{ url('/') }}";

        $(".openTabButton").on("click", function(event) {
            event.stopPropagation(); 
           
           var admission_id = $(this).attr('data-admission_id');
           thisTarget = $(this);
            var newWindow = window.open(basurl+'/admissionEdit/'+admission_id, '_blank');

            var checkWindow = setInterval(function() {
                if (newWindow.closed) {
                    clearInterval(checkWindow); // Stop checking when the tab is closed
                    onTabClosed(); // Call the callback function
               

               }
            }, 1000); // Check every 1 second
        });

        function onTabClosed() {
            // Show toastr message in modal
            $("#toastrMessage").html(`1.) The data has been updated.<br>2.) If the updated data is not reflected, please refresh the page once.`);
            $("#toastrModal").modal('show');

            // Close modal after 2 seconds
            setTimeout(function() {
                $("#toastrModal").modal('hide');
           thisTarget.closest('tr').trigger('click');
            }, 2000);
        }

       
    });
</script>

<script>
$(document).ready(function() {
    function updateClassDropdown(classSelect, data) {
        classSelect.empty();
        classSelect.append('<option value="">{{ __("common.Select") }}</option>');
        if (data && data.length > 0) {
            $.each(data, function(key, val) {
                classSelect.append('<option value="' + val.id + '">' + val.name + '</option>');
            });
        }
        if (classSelect.hasClass("select2-hidden-accessible")) {
            classSelect.select2('destroy');
        }
        classSelect.select2();
        classSelect.val('').trigger('change');
    }

    $(document).on('change', '#course_id', function() {
        var courseId = $(this).val();
        var classSelect = $('#class_type_id');
        if (courseId) {
            $.ajax({
                url: "{{ url('getClassesByCourse') }}",
                type: "GET",
                data: { course_id: courseId },
                dataType: "json",
                success: function(data) {
                    updateClassDropdown(classSelect, data);
                },
                error: function() {
                    updateClassDropdown(classSelect, []);
                }
            });
        } else {
            var defaultClasses = [];
            @if(!empty($allClassType))
                @foreach($allClassType as $type)
                    defaultClasses.push({ id: "{{ $type->id }}", name: "{{ $type->name }}" });
                @endforeach
            @endif
            updateClassDropdown(classSelect, defaultClasses);
        }
    });

    var hiddenColumns = [];

    function toggleColumns() {
        var columnsToToggle = [];

        $('td.child').each(function() {
            var isZero = $(this).attr('data-isZero') == 0;
            var key = $(this).attr('data-key');
            var columnId = 'key_' + key;

            if (isZero) {
                columnsToToggle.push(columnId);
            }
        });

        columnsToToggle.forEach(function(columnId) {
            var columnIndex = $('th#' + columnId).index();

            if (columnIndex === -1) {
                console.log('Column with id ' + columnId + ' not found.');
                return;
            }

            var isHidden = hiddenColumns.includes(columnId);

            if (isHidden) {
                $('table thead tr').each(function() {
                    $(this).find('th').eq(columnIndex).show();
                });
                $('table tbody tr').each(function() {
                    $(this).find('td').eq(columnIndex).show();
                });

                hiddenColumns = hiddenColumns.filter(function(id) {
                    return id !== columnId;
                });
            } else {
                $('table thead tr').each(function() {
                    $(this).find('th').eq(columnIndex).hide();
                });
                $('table tbody tr').each(function() {
                    $(this).find('td').eq(columnIndex).hide();
                });

                hiddenColumns.push(columnId);
            }
        });
    }

    toggleColumns();

    $('#toggle-columns').click(function() {
        toggleColumns();
        
        var value = $(this).text().trim();
         $('#toggle-columns').text(value == 'Show All Head' ? 'Hide Head' : 'Show All Head')
    });
});
    </script>
<style>
    .openTabButton{
      color: #570bff;    }
      .table th, .table td {
  padding: 0 0px;
}

.table-bordered td, .table-bordered th {
  border: 2px solid #000;
}
</style>
@endsection