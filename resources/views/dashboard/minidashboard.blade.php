@php
   $sidebar = Helper::getSiderbar();
   $getPermisn = Helper::getPermisn();
   $getPermisnByBranch = Helper::getPermisnByBranch();
   $getSetting = Helper::getSetting();
   $allPermisn = !empty($getPermisn['sidebar_id']) ? explode(',', $getPermisn['sidebar_id']) : [];
   if(in_array(11, $allPermisn) && !in_array(29, $allPermisn)) {
       $allPermisn[] = 29;
   }
@endphp
@extends('layout.app')
@section('content')

<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">
        <div class="Display_none_PC">
            <div class="col-md-12 p-0">
                <div class="card title_dash p-2 bg-primary">
                   <h3 class="card-title text-white"><i class="fa fa-home"></i> &nbsp;{{ __('Mini Dashboard')  }}</h3>
                </div>
            </div>
            <div class="row">
                 @if(!empty($sidebar))
                 @php
                $colors = ['warning', 'success', 'primary', 'dark', 'danger', 'info','secondary'];
                $previousColor = '';
                @endphp
                    @foreach($sidebar as $data)
                        @foreach($allPermisn as $permisnData)
                            @if($data['id'] == $permisnData)
                            @php
                            do {
                                $colorClass = $colors[array_rand($colors)];
                            } while ($colorClass == $previousColor);
                            $previousColor = $colorClass;
                            @endphp
                             <div class="col-6">
                                    <a href="{{url($data['url'])}}">
                                    <div class="card mobile_dashboard_card">
                                            <div class="box_icon bg-{{$colorClass}}">
                                           <i class="{{$data['ican'] ?? ''}}"></i>
                                           </div>
                                            <div class="info_text_box">
                                                
                                                @if(Session::get('locale') == 'hi')
                                                <p class="info-box-text">{{$data['hindi_name'] ?? ''}}</p>
                                                @else
                                                    <p class="info-box-text">{{$data['name'] ?? ''}}</p>
                                                @endif
                                            </div>
                                    </div>
                                    </a>
                            </div>
                            @endif
                        @endforeach
                    @endforeach
                @endif
                
                <div class="col-6">
                                    <a href="{{url('logout')}}">
                                    <div class="card mobile_dashboard_card">
                                            <div class="box_icon bg-danger">
                                           <i class="fa fa-sign-out"></i>
                                           </div>
                                            <div class="info_text_box">
                                            <p class="info-box-text">Log Out</p>
                                    </div>
                            </div>
                        </a>
                </div>
        </div>
        </div>
        </div>
    </section>

</div>

@endsection