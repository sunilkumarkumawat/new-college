@php
$noticeBoard = Helper::noticeBoard();
$task = Helper::task();
@endphp
@extends('layout.app') 
@section('content')

<div class="content-wrapper" >

   <section class="content pt-3">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 col-md-12">
            <div class="card card-outline card-orange">
                <div class="card-header bg-primary">
                    <h3 class="card-title"><i class="fa fa-home"></i> &nbsp; Principal Dashboard</h3>
                    <div class="card-tools">
                        <!--<a href="{{url('add_user')}}" class="btn btn-primary  btn-sm" title="Add User"><i class="fa fa-plus"></i> Add User</a>-->
                    </div>
            
                </div>               
            </div>
            </div>
        </div>
        
        <div class="row">

                <div class="col-12 col-sm-6 col-md-3">
                    <a href="{{ url('admission/index') }}">
                        <div class="info-box mb-3 text-dark">
                            <span class="info-box-icon bg-success elevation-1"><i
                                    class="fa fa-graduation-cap"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('dashboard.TOTAL STUDENTS') }}</span>
                                <span class="info-box-number">{{\App\Models\Admission::countActiveAdmission()}}</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <a href="{{ url('students/attendance/view') }}">
                        <div class="info-box mb-3 text-dark">
                            <span class="info-box-icon bg-danger elevation-1"><i class="ion ion-stats-bars"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('dashboard.PRESENT STUDENTS') }}</span>
                                <span class="info-box-number">{{\App\Models\StudentAttendance::countPresentStudents()
                                    }}</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <a href="{{ url('users/view') }}">
                        <div class="info-box mb-3 text-dark">
                            <span class="info-box-icon bg-primary elevation-1"><i class="fa fa-shirtsinbulk"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('dashboard.TOTAL TEACHERS') }}</span>
                                <span class="info-box-number">{{\App\Models\User::countUser() }}</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <a href="{{ url('staff/Attendance/view') }}">
                        <div class="info-box mb-3 text-dark">
                            <span class="info-box-icon bg-warning elevation-1"><i class="ion ion-stats-bars"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('dashboard.PRESENT TEACHERS') }}</span>
                                <span class="info-box-number">{{\App\Models\TeacherAttendance::countTeacherAttendance()
                                    }}</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <a href="{{ url('Fees/add') }}">
                        <div class="info-box mb-3 text-dark">
                            <span class="info-box-icon bg-danger elevation-1"><i class="fa fa-money"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('dashboard.FEES COLLECTION') }}</span>
                                <span class="info-box-number"><i class="fa fa-rupee"></i>
                                    {{\App\Models\FeesDetail::totalCollection() }}</span>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <a href="{{ url('expense_view') }}">
                        <div class="info-box mb-3 text-dark">
                            <span class="info-box-icon bg-warning elevation-1"><i class="fa fa-credit-card"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('dashboard.MONTHLY EXPENSES') }}</span>
                                <span class="info-box-number"><i class="fa fa-rupee"></i>
                                    {{\App\Models\Expense::totalExpense()}}</span>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <a href="{{ url('complaint_view') }}">
                        <div class="info-box mb-3 text-dark">
                            <span class="info-box-icon bg-warning elevation-1"><i class="fa fa-snapchat"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('dashboard.TOTAL COMPLAINTS') }}</span>
                                <span class="info-box-number">1</span>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <a href="{{ url('invantory_dashboard') }}">
                        <div class="info-box mb-3 text-dark">
                            <span class="info-box-icon bg-danger elevation-1"><i class="fa fa-archive"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('dashboard.INVANTORY') }}</span>
                                <span class="info-box-number"> {{\App\Models\Invantory::countInvantory() }}</span>
                            </div>
                        </div>
                    </a>
                </div>
            
            <div class="col-12 col-sm-6 col-md-3">
                <a href="{{ url('add_class') }}">
                <div class="info-box mb-3 text-dark">
                    <span class="info-box-icon bg-danger elevation-1"><i class="ion ion-stats-bars"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">TOTAL CLASS</span>
                    <span class="info-box-number">{{\App\Models\ClassType::countClasss() ?? '0' }}</span>
                    </div>
                </div>
                </a>
            </div>
  
            <div class="col-12 col-sm-6 col-md-3">
                <a href="{{ url('library_add') }}">
                <div class="info-box mb-3 text-dark">
                    <span class="info-box-icon bg-warning elevation-1"><i class="fa fa-book"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">TOTAL LIBRARY</span>
                    <span class="info-box-number">{{\App\Models\library\Library::countTotelLibrary() }}</span>
                    </div>
                </div>
                </a>
            </div>             
            <div class="col-12 col-sm-6 col-md-3">
                <a href="{{ url('hostel_add') }}">
                <div class="info-box mb-3 text-dark">
                    <span class="info-box-icon bg-danger elevation-1"><i class="fa fa-hospital-o" ></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">TOTAL HOSTEL</span>
                    <span class="info-box-number">{{\App\Models\hostel\Hostel::countTotelHostel() }}</span>
                    </div>
                </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <a href="{{ url('bus/index') }}">
                <div class="info-box mb-3 text-dark">
                    <span class="info-box-icon bg-primary elevation-1"><i class="fa fa-bus"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">TOTAL BUS</span>
                    <span class="info-box-number">{{\App\Models\Master\Bus::countBus() }}</span>
                    </div>
                </div>
                </a>
            </div>
                  
        </div>        
        
        <div class="row">
            <div class="col-md-6" id="calendarElement">

            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                <div class="card-header ui-sortable-handle">
                <h3 class="card-title">
                <i class="ion ion-clipboard mr-1"></i>
                To Do List
                </h3>
                <div class="card-tools" style="width:70%;">
                    <div class="row">
                        <div class="col-md-8">
                            <input type="text" class="form-control form-control-border" id="task" name="task" placeholder="Enter Task..">
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="add_task btn btn-primary float-right btn-xs"><i class="fa fa-plus"></i> Add</button>
                        </div>
                    </div>
                
                </div>
                </div>
                
                <div class="card-body">
                <ul class="todo-list ui-sortable" data-widget="todo-list">
                @if(!empty($task))
                @foreach($task as $item)
                    <li class="" id="_{{ $item->id ?? '' }}">
                    <span class="handle ui-sortable-handle">
                    <i class="fa fa-ellipsis-v"></i>
                    <i class="fa fa-ellipsis-v"></i>
                    </span>
                    <div class="icheck-primary d-inline ml-2">
                    <input type="checkbox" value="" class="task_status" data-id="{{ $item->id ?? '' }}" data-status="{{ $item->status ?? '' }}" name="task_status" id="_{{ $item->name ?? '' }}" style="{{  $item['status'] == 1 ? 'checked' : '' }}">
                    <label for="_{{ $item->name ?? '' }}"></label>
                    </div>
                    <span class="text">{{ $item->name ?? '' }}</span>
                    <small class="badge badge-primary"><i class="fa fa-clock"></i> 1 week</small>
                    <div class="tools">
                    <i class="fa fa-trash-o task_delete" data-id="{{ $item->id ?? '' }}"></i>
                    </div>
                    </li>
                @endforeach
                @endif
                </ul>
                </div>

                </div>        
            </div>
            <div class="col-md-6">
                <div class="card">
                <div class="card-header ui-sortable-handle">
                <h3 class="card-title">
                <i class="fa fa-bell-o mr-1"></i>
                Notifications
                </h3>

                </div>
                
                <div class="card-body">
                    <marquee direction="up" scrollamount="4" id="test" onMouseOver="document.all.test.stop()" onMouseOut="document.all.test.start()">
                        <ul class="todo-list ui-sortable" data-widget="todo-list">
                            @if(!empty($noticeBoard))
                            @foreach($noticeBoard as $item)
                                <li class="">
                                    <a href="{{ url('notice_board/view') }}">
                                    <span class="text text-dark">{{ $item->title ?? '' }}</span>
                                    <small class="badge badge-danger"><i class="fa fa-envelope-o"></i> New</small>
                                    </a>
                                </li>
                            @endforeach
                            @endif
                        </ul>
                    </marquee>
                </div>

                </div>        
            </div>
        </div>
        
<div class="row">
<div class="col-md-6">

<div class="card card-primary">
<div class="card-header">
<h3 class="card-title">Area Chart</h3>
<div class="card-tools">
<button type="button" class="btn btn-tool" data-card-widget="collapse">
<i class="fa fa-minus"></i>
</button>
<button type="button" class="btn btn-tool" data-card-widget="remove">
<i class="fa fa-times"></i>
</button>
</div>
</div>
<div class="card-body">
<div class="chart"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
<canvas id="areaChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%; display: block; width: 487px;" class="chartjs-render-monitor" width="487" height="250"></canvas>
</div>
</div>

</div>


<div class="card card-danger">
<div class="card-header">
<h3 class="card-title">Donut Chart</h3>
<div class="card-tools">
<button type="button" class="btn btn-tool" data-card-widget="collapse">
<i class="fa fa-minus"></i>
</button>
<button type="button" class="btn btn-tool" data-card-widget="remove">
<i class="fa fa-times"></i>
</button>
</div>
</div>
<div class="card-body"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
<canvas id="donutChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%; display: block; width: 487px;" class="chartjs-render-monitor" width="487" height="250"></canvas>
</div>

</div>


<div class="card card-danger">
<div class="card-header">
<h3 class="card-title">Pie Chart</h3>
<div class="card-tools">
<button type="button" class="btn btn-tool" data-card-widget="collapse">
<i class="fa fa-minus"></i>
</button>
<button type="button" class="btn btn-tool" data-card-widget="remove">
<i class="fa fa-times"></i>
</button>
</div>
</div>
<div class="card-body"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
<canvas id="pieChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%; display: block; width: 487px;" class="chartjs-render-monitor" width="487" height="250"></canvas>
</div>

</div>

</div>

<div class="col-md-6">

<div class="card card-info">
<div class="card-header">
<h3 class="card-title">Line Chart</h3>
<div class="card-tools">
<button type="button" class="btn btn-tool" data-card-widget="collapse">
<i class="fa fa-minus"></i>
</button>
<button type="button" class="btn btn-tool" data-card-widget="remove">
<i class="fa fa-times"></i>
</button>
</div>
</div>
<div class="card-body">
<div class="chart"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
<canvas id="lineChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%; display: block; width: 487px;" class="chartjs-render-monitor" width="487" height="250"></canvas>
</div>
</div>

</div>


<div class="card card-success">
<div class="card-header">
<h3 class="card-title">Bar Chart</h3>
<div class="card-tools">
<button type="button" class="btn btn-tool" data-card-widget="collapse">
<i class="fa fa-minus"></i>
</button>
<button type="button" class="btn btn-tool" data-card-widget="remove">
<i class="fa fa-times"></i>
</button>
</div>
</div>
<div class="card-body">
<div class="chart"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
<canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%; display: block; width: 487px;" class="chartjs-render-monitor" width="487" height="250"></canvas>
</div>
</div>

</div>


<div class="card card-success">
<div class="card-header">
<h3 class="card-title">Stacked Bar Chart</h3>
<div class="card-tools">
<button type="button" class="btn btn-tool" data-card-widget="collapse">
<i class="fa fa-minus"></i>
</button>
<button type="button" class="btn btn-tool" data-card-widget="remove">
<i class="fa fa-times"></i>
</button>
</div>
</div>
<div class="card-body">
<div class="chart"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
<canvas id="stackedBarChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%; display: block; width: 487px;" class="chartjs-render-monitor" width="487" height="250"></canvas>
</div>
</div>

</div>

</div>

</div>        
        
		<div class="row">
			<div class="col-md-3">
				<div class="sticky-top mb-3">
					<div class="card">
						<div class="card-header">
							<h4 class="card-title">Draggable Events</h4> </div>
						<div class="card-body">
							<div id="external-events">
								<div class="external-event bg-success ui-draggable ui-draggable-handle" style="position: relative;">Lunch</div>
								<div class="external-event bg-warning ui-draggable ui-draggable-handle" style="position: relative;">Go home</div>
								<div class="external-event bg-info ui-draggable ui-draggable-handle" style="position: relative;">Do homework</div>
								<div class="external-event bg-primary ui-draggable ui-draggable-handle" style="position: relative;">Work on UI design</div>
								<div class="external-event bg-danger ui-draggable ui-draggable-handle" style="position: relative;">Sleep tight</div>
								<div class="checkbox">
									<label for="drop-remove">
										<input type="checkbox" id="drop-remove"> remove after drop </label>
								</div>
							</div>
						</div>
					</div>
					<div class="card">
						<div class="card-header">
							<h3 class="card-title">Create Event</h3> </div>
						<div class="card-body">
							<div class="btn-group" style="width: 100%; margin-bottom: 10px;">
								<ul class="fc-color-picker" id="color-chooser">
									<li><a class="text-primary" href="#"><i class="fa fa-square"></i></a></li>
									<li><a class="text-warning" href="#"><i class="fa fa-square"></i></a></li>
									<li><a class="text-success" href="#"><i class="fa fa-square"></i></a></li>
									<li><a class="text-danger" href="#"><i class="fa fa-square"></i></a></li>
									<li><a class="text-muted" href="#"><i class="fa fa-square"></i></a></li>
								</ul>
							</div>
							<div class="input-group">
								<input id="new-event" type="text" class="form-control" placeholder="Event Title">
								<div class="input-group-append">
									<button id="add-new-event" type="button" class="btn btn-primary">Add</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-9">
				<div class="card card-primary">
					<div class="card-body p-0">
						<div id="calendar" class="fc fc-media-screen fc-direction-ltr fc-theme-bootstrap">
							<div class="fc-header-toolbar fc-toolbar fc-toolbar-ltr">
								<div class="fc-toolbar-chunk">
									<div class="btn-group">
										<button type="button" title="Previous month" aria-pressed="false" class="fc-prev-button btn btn-primary"><span class="fa fa-chevron-left"></span></button>
										<button type="button" title="Next month" aria-pressed="false" class="fc-next-button btn btn-primary"><span class="fa fa-chevron-right"></span></button>
									</div>
									<button type="button" title="This month" disabled="" aria-pressed="false" class="fc-today-button btn btn-primary">today</button>
								</div>
								<div class="fc-toolbar-chunk">
									<h2 class="fc-toolbar-title" id="fc-dom-1">April 2022</h2></div>
								<div class="fc-toolbar-chunk">
									<div class="btn-group">
										<button type="button" title="month view" aria-pressed="true" class="fc-dayGridMonth-button btn btn-primary active">month</button>
										<button type="button" title="week view" aria-pressed="false" class="fc-timeGridWeek-button btn btn-primary">week</button>
										<button type="button" title="day view" aria-pressed="false" class="fc-timeGridDay-button btn btn-primary">day</button>
									</div>
								</div>
							</div>
							<div aria-labelledby="fc-dom-1" class="fc-view-harness fc-view-harness-active" style="height: 590.37px;">
								<div class="fc-daygrid fc-dayGridMonth-view fc-view">
									<table role="grid" class="fc-scrollgrid table-bordered fc-scrollgrid-liquid">
										<thead role="rowgroup">
											<tr role="presentation" class="fc-scrollgrid-section fc-scrollgrid-section-header ">
												<th role="presentation">
													<div class="fc-scroller-harness">
														<div class="fc-scroller" style="overflow: hidden;">
															<table role="presentation" class="fc-col-header " style="width: 795px;">
																<colgroup></colgroup>
																<thead role="presentation">
																	<tr role="row">
																		<th role="columnheader" class="fc-col-header-cell fc-day fc-day-sun">
																			<div class="fc-scrollgrid-sync-inner"><a aria-label="Sunday" class="fc-col-header-cell-cushion ">Sun</a></div>
																		</th>
																		<th role="columnheader" class="fc-col-header-cell fc-day fc-day-mon">
																			<div class="fc-scrollgrid-sync-inner"><a aria-label="Monday" class="fc-col-header-cell-cushion ">Mon</a></div>
																		</th>
																		<th role="columnheader" class="fc-col-header-cell fc-day fc-day-tue">
																			<div class="fc-scrollgrid-sync-inner"><a aria-label="Tuesday" class="fc-col-header-cell-cushion ">Tue</a></div>
																		</th>
																		<th role="columnheader" class="fc-col-header-cell fc-day fc-day-wed">
																			<div class="fc-scrollgrid-sync-inner"><a aria-label="Wednesday" class="fc-col-header-cell-cushion ">Wed</a></div>
																		</th>
																		<th role="columnheader" class="fc-col-header-cell fc-day fc-day-thu">
																			<div class="fc-scrollgrid-sync-inner"><a aria-label="Thursday" class="fc-col-header-cell-cushion ">Thu</a></div>
																		</th>
																		<th role="columnheader" class="fc-col-header-cell fc-day fc-day-fri">
																			<div class="fc-scrollgrid-sync-inner"><a aria-label="Friday" class="fc-col-header-cell-cushion ">Fri</a></div>
																		</th>
																		<th role="columnheader" class="fc-col-header-cell fc-day fc-day-sat">
																			<div class="fc-scrollgrid-sync-inner"><a aria-label="Saturday" class="fc-col-header-cell-cushion ">Sat</a></div>
																		</th>
																	</tr>
																</thead>
															</table>
														</div>
													</div>
												</th>
											</tr>
										</thead>
										<tbody role="rowgroup">
											<tr role="presentation" class="fc-scrollgrid-section fc-scrollgrid-section-body  fc-scrollgrid-section-liquid">
												<td role="presentation">
													<div class="fc-scroller-harness fc-scroller-harness-liquid">
														<div class="fc-scroller fc-scroller-liquid-absolute" style="overflow: hidden auto;">
															<div class="fc-daygrid-body fc-daygrid-body-unbalanced " style="width: 795px;">
																<table role="presentation" class="fc-scrollgrid-sync-table" style="width: 795px; height: 558px;">
																	<colgroup></colgroup>
																	<tbody role="presentation">
																		<tr role="row">
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-sun fc-day-past fc-day-other" data-date="2022-03-27" aria-labelledby="fc-dom-102">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-102" class="fc-daygrid-day-number" aria-label="March 27, 2022">27</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-mon fc-day-past fc-day-other" data-date="2022-03-28" aria-labelledby="fc-dom-104">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-104" class="fc-daygrid-day-number" aria-label="March 28, 2022">28</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-tue fc-day-past fc-day-other" data-date="2022-03-29" aria-labelledby="fc-dom-106">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-106" class="fc-daygrid-day-number" aria-label="March 29, 2022">29</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-wed fc-day-past fc-day-other" data-date="2022-03-30" aria-labelledby="fc-dom-108">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-108" class="fc-daygrid-day-number" aria-label="March 30, 2022">30</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-thu fc-day-past fc-day-other" data-date="2022-03-31" aria-labelledby="fc-dom-110">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-110" class="fc-daygrid-day-number" aria-label="March 31, 2022">31</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-fri fc-day-past" data-date="2022-04-01" aria-labelledby="fc-dom-112">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-112" class="fc-daygrid-day-number" aria-label="April 1, 2022">1</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-event-harness" style="margin-top: 0px;">
																							<a class="fc-daygrid-event fc-daygrid-block-event fc-h-event fc-event fc-event-draggable fc-event-resizable fc-event-start fc-event-end fc-event-past" style="border-color: rgb(245, 105, 84); background-color: rgb(245, 105, 84);">
																								<div class="fc-event-main">
																									<div class="fc-event-main-frame">
																										<div class="fc-event-title-container">
																											<div class="fc-event-title fc-sticky">All Day Event</div>
																										</div>
																									</div>
																								</div>
																								<div class="fc-event-resizer fc-event-resizer-end"></div>
																							</a>
																						</div>
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-sat fc-day-past" data-date="2022-04-02" aria-labelledby="fc-dom-114">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-114" class="fc-daygrid-day-number" aria-label="April 2, 2022">2</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																		</tr>
																		<tr role="row">
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-sun fc-day-past" data-date="2022-04-03" aria-labelledby="fc-dom-116">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-116" class="fc-daygrid-day-number" aria-label="April 3, 2022">3</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-mon fc-day-past" data-date="2022-04-04" aria-labelledby="fc-dom-118">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-118" class="fc-daygrid-day-number" aria-label="April 4, 2022">4</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-tue fc-day-past" data-date="2022-04-05" aria-labelledby="fc-dom-120">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-120" class="fc-daygrid-day-number" aria-label="April 5, 2022">5</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-wed fc-day-past" data-date="2022-04-06" aria-labelledby="fc-dom-122">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-122" class="fc-daygrid-day-number" aria-label="April 6, 2022">6</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-thu fc-day-past" data-date="2022-04-07" aria-labelledby="fc-dom-124">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-124" class="fc-daygrid-day-number" aria-label="April 7, 2022">7</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-fri fc-day-past" data-date="2022-04-08" aria-labelledby="fc-dom-126">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-126" class="fc-daygrid-day-number" aria-label="April 8, 2022">8</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-sat fc-day-past" data-date="2022-04-09" aria-labelledby="fc-dom-128">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-128" class="fc-daygrid-day-number" aria-label="April 9, 2022">9</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																		</tr>
																		<tr role="row">
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-sun fc-day-past" data-date="2022-04-10" aria-labelledby="fc-dom-130">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-130" class="fc-daygrid-day-number" aria-label="April 10, 2022">10</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-mon fc-day-past" data-date="2022-04-11" aria-labelledby="fc-dom-132">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-132" class="fc-daygrid-day-number" aria-label="April 11, 2022">11</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-tue fc-day-past" data-date="2022-04-12" aria-labelledby="fc-dom-134">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-134" class="fc-daygrid-day-number" aria-label="April 12, 2022">12</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-wed fc-day-past" data-date="2022-04-13" aria-labelledby="fc-dom-136">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-136" class="fc-daygrid-day-number" aria-label="April 13, 2022">13</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-thu fc-day-past" data-date="2022-04-14" aria-labelledby="fc-dom-138">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-138" class="fc-daygrid-day-number" aria-label="April 14, 2022">14</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-fri fc-day-past" data-date="2022-04-15" aria-labelledby="fc-dom-140">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-140" class="fc-daygrid-day-number" aria-label="April 15, 2022">15</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-sat fc-day-past" data-date="2022-04-16" aria-labelledby="fc-dom-142">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-142" class="fc-daygrid-day-number" aria-label="April 16, 2022">16</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																		</tr>
																		<tr role="row">
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-sun fc-day-past" data-date="2022-04-17" aria-labelledby="fc-dom-144">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-144" class="fc-daygrid-day-number" aria-label="April 17, 2022">17</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-mon fc-day-past" data-date="2022-04-18" aria-labelledby="fc-dom-146">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-146" class="fc-daygrid-day-number" aria-label="April 18, 2022">18</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-tue fc-day-past" data-date="2022-04-19" aria-labelledby="fc-dom-148">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-148" class="fc-daygrid-day-number" aria-label="April 19, 2022">19</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-wed fc-day-past" data-date="2022-04-20" aria-labelledby="fc-dom-150">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-150" class="fc-daygrid-day-number" aria-label="April 20, 2022">20</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-thu fc-day-past" data-date="2022-04-21" aria-labelledby="fc-dom-152">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-152" class="fc-daygrid-day-number" aria-label="April 21, 2022">21</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-fri fc-day-past" data-date="2022-04-22" aria-labelledby="fc-dom-154">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-154" class="fc-daygrid-day-number" aria-label="April 22, 2022">22</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-sat fc-day-past" data-date="2022-04-23" aria-labelledby="fc-dom-156">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-156" class="fc-daygrid-day-number" aria-label="April 23, 2022">23</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-event-harness" style="margin-top: 0px;">
																							<a class="fc-daygrid-event fc-daygrid-block-event fc-h-event fc-event fc-event-draggable fc-event-start fc-event-past" style="border-color: rgb(243, 156, 18); background-color: rgb(243, 156, 18);">
																								<div class="fc-event-main">
																									<div class="fc-event-main-frame">
																										<div class="fc-event-time">12a</div>
																										<div class="fc-event-title-container">
																											<div class="fc-event-title fc-sticky">Long Event</div>
																										</div>
																									</div>
																								</div>
																							</a>
																						</div>
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																		</tr>
																		<tr role="row">
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-sun fc-day-past" data-date="2022-04-24" aria-labelledby="fc-dom-158">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-158" class="fc-daygrid-day-number" aria-label="April 24, 2022">24</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-event-harness fc-daygrid-event-harness-abs" style="top: 0px; left: 0px; right: -113.562px;">
																							<a class="fc-daygrid-event fc-daygrid-block-event fc-h-event fc-event fc-event-draggable fc-event-end fc-event-past" style="border-color: rgb(243, 156, 18); background-color: rgb(243, 156, 18);">
																								<div class="fc-event-main">
																									<div class="fc-event-main-frame">
																										<div class="fc-event-time">12a</div>
																										<div class="fc-event-title-container">
																											<div class="fc-event-title fc-sticky">Long Event</div>
																										</div>
																									</div>
																								</div>
																							</a>
																						</div>
																						<div class="fc-daygrid-day-bottom" style="margin-top: 25px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-mon fc-day-past" data-date="2022-04-25" aria-labelledby="fc-dom-160">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-160" class="fc-daygrid-day-number" aria-label="April 25, 2022">25</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 25px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-tue fc-day-past" data-date="2022-04-26" aria-labelledby="fc-dom-162">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-162" class="fc-daygrid-day-number" aria-label="April 26, 2022">26</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-wed fc-day-past" data-date="2022-04-27" aria-labelledby="fc-dom-164">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-164" class="fc-daygrid-day-number" aria-label="April 27, 2022">27</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-thu fc-day-today " data-date="2022-04-28" aria-labelledby="fc-dom-166">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-166" class="fc-daygrid-day-number" aria-label="April 28, 2022">28</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-event-harness" style="margin-top: 0px;">
																							<a class="fc-daygrid-event fc-daygrid-dot-event fc-event fc-event-draggable fc-event-resizable fc-event-start fc-event-end fc-event-today" href="https://www.google.com/">
																								<div class="fc-daygrid-event-dot" style="border-color: rgb(60, 141, 188);"></div>
																								<div class="fc-event-time">12a</div>
																								<div class="fc-event-title">Click for Google</div>
																							</a>
																						</div>
																						<div class="fc-daygrid-event-harness" style="margin-top: 0px;">
																							<a class="fc-daygrid-event fc-daygrid-dot-event fc-event fc-event-draggable fc-event-resizable fc-event-start fc-event-end fc-event-today">
																								<div class="fc-daygrid-event-dot" style="border-color: rgb(0, 115, 183);"></div>
																								<div class="fc-event-time">10:30a</div>
																								<div class="fc-event-title">Meeting</div>
																							</a>
																						</div>
																						<div class="fc-daygrid-event-harness" style="margin-top: 0px;">
																							<a class="fc-daygrid-event fc-daygrid-dot-event fc-event fc-event-draggable fc-event-resizable fc-event-start fc-event-end fc-event-today">
																								<div class="fc-daygrid-event-dot" style="border-color: rgb(0, 192, 239);"></div>
																								<div class="fc-event-time">12p</div>
																								<div class="fc-event-title">Lunch</div>
																							</a>
																						</div>
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-fri fc-day-future" data-date="2022-04-29" aria-labelledby="fc-dom-168">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-168" class="fc-daygrid-day-number" aria-label="April 29, 2022">29</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-event-harness" style="margin-top: 0px;">
																							<a class="fc-daygrid-event fc-daygrid-dot-event fc-event fc-event-draggable fc-event-resizable fc-event-start fc-event-end fc-event-future">
																								<div class="fc-daygrid-event-dot" style="border-color: rgb(0, 166, 90);"></div>
																								<div class="fc-event-time">7p</div>
																								<div class="fc-event-title">Birthday Party</div>
																							</a>
																						</div>
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-sat fc-day-future" data-date="2022-04-30" aria-labelledby="fc-dom-170">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-170" class="fc-daygrid-day-number" aria-label="April 30, 2022">30</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																		</tr>
																		<tr role="row">
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-sun fc-day-future fc-day-other" data-date="2022-05-01" aria-labelledby="fc-dom-172">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-172" class="fc-daygrid-day-number" aria-label="May 1, 2022">1</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-mon fc-day-future fc-day-other" data-date="2022-05-02" aria-labelledby="fc-dom-174">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-174" class="fc-daygrid-day-number" aria-label="May 2, 2022">2</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-tue fc-day-future fc-day-other" data-date="2022-05-03" aria-labelledby="fc-dom-176">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-176" class="fc-daygrid-day-number" aria-label="May 3, 2022">3</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-wed fc-day-future fc-day-other" data-date="2022-05-04" aria-labelledby="fc-dom-178">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-178" class="fc-daygrid-day-number" aria-label="May 4, 2022">4</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-thu fc-day-future fc-day-other" data-date="2022-05-05" aria-labelledby="fc-dom-180">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-180" class="fc-daygrid-day-number" aria-label="May 5, 2022">5</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-fri fc-day-future fc-day-other" data-date="2022-05-06" aria-labelledby="fc-dom-182">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-182" class="fc-daygrid-day-number" aria-label="May 6, 2022">6</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																			<td role="gridcell" class="fc-daygrid-day fc-day fc-day-sat fc-day-future fc-day-other" data-date="2022-05-07" aria-labelledby="fc-dom-184">
																				<div class="fc-daygrid-day-frame fc-scrollgrid-sync-inner">
																					<div class="fc-daygrid-day-top"><a id="fc-dom-184" class="fc-daygrid-day-number" aria-label="May 7, 2022">7</a></div>
																					<div class="fc-daygrid-day-events">
																						<div class="fc-daygrid-day-bottom" style="margin-top: 0px;"></div>
																					</div>
																					<div class="fc-daygrid-day-bg"></div>
																				</div>
																			</td>
																		</tr>
																	</tbody>
																</table>
															</div>
														</div>
													</div>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
   


        
        </div>
    </section>

</div>

<script>
$(document).on('click', ".add_task", function () {
    var task = $('#task').val();
    var data = {'task': task}
        $.ajaxSetup({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
        });
        $.ajax({
            type: "POST",
            url: "/add/task",
            data: data,
            dataType: "html",
            success: function (response) {
            toastr.success('Task Added Successfully.');
            },
        });
});

$(document).on('click', ".task_status", function () {
    var id = $(this).data('id');
    var status = $(this).data('status');
		$.ajax({
			url: '/status/task',
			type: 'post',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			data: {
				status: status,
				id: id
			},
			success: function() {
				toastr.success('Record Saved Successfully.');
			},
		});    
});

$(document).on('click', ".task_delete", function () {
    var task_id = $(this).data('id');
    var data = {'task_id': task_id}
        $.ajaxSetup({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
        });
        $.ajax({
            type: "POST",
            url: "/delete/task",
            data: data,
            dataType: "html",
            success: function (response) {
               $("#task_li").remove();
            toastr.success('Task Deleted Successfully.');
            },
        });
});
</script>
@endsection
  
