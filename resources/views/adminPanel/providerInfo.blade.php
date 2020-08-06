@extends('adminPanel.layout.app') @section('content')
<!-- Bet History content start -->
<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="app-menu__icon fa fa-th-list"></i> {{__('adminPanel.providerInfo')}}</h1>
            <p>{{__('adminPanel.dashboardDesc')}}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb side">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item active">{{__('adminPanel.provider')}}</li>
            <li class="breadcrumb-item active"><a href="{!!route('vProviderInfo')!!}">{{__('adminPanel.providerInfo')}}</a></li>
        </ul>
    </div>
    <?php $value = session(str_replace(".","_",request()->ip()).'ECGames'); ?>
    <div class="row">
        <div class="col-md-3">
        </div>
        <div class="col-md-6">
            <div class="tile">
            <form action="{{ route('vUpdateProviderInfo') }}" method="POST" class="providerInfo">
                {{csrf_field()}}
                    <div class="row">
                        <div class="col-lg-12">
                            @if ($value['isAllowAll'] == 'true')
                            <div class="form-group">
                                <label class="control-label">{{__('adminPanel.selectPortalProvider')}}</label>
                                <div class="form-group">
                                    <div class="form-group-select">
                                        <select class="form-control" name="portalProviderPID">
                                            <option value="">{{__('adminPanel.selectPortalProvider')}}</option>
                                                @foreach ($portalProviderData as $key => $portalProvider)
                                                @if($portalProvider->PID != 1)
                                                    <option value="{{$portalProvider->PID}}">{{$portalProvider->name}}</option>
                                                @endif
                                                @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            @else 
                             <input name="portalProviderPID" value="{{$value['portalProviderID']}}" hidden> {{-- use check validate --}}
                            @endif

                            <div class="form-group">
                                <label class="control-label">{{__('adminPanel.webSiteName')}}</label>
                                <div class="form-group">
                                    <div class="input-group">
                                        <input class="form-control" value="{{ $serverName }}" name="serverName" type="text" placeholder="{{__('adminPanel.webSiteName')}}">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label">{{__('adminPanel.lastIP')}}</label>
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="ipaddress" value="{{ $ipList }}" name="ipList" placeholder="xxx.xxx.xxx.xxx"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label">{{__('adminPanel.APIKey')}}</label>
                                <div class="form-group">
                                    <div class="input-group">
                                        <input class="form-control" value="{{ $APIKey }}" name="APIKey" maxlength = "255" type="text"  placeholder="{{__('adminPanel.APIKey')}}">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label">{{__('adminPanel.currencyType')}}</label>
                                <div class="form-group">
                                    <div class="input-group">
                                        <input class="form-control" value="{{ $currency }}" type="text"  placeholder="{{__('adminPanel.currencyType')}}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <button class="btn btn-primary" type="submit">{{__('adminPanel.update')}}</button>
                        <button class="btn btn-danger" type="reset">{{__('adminPanel.cancel')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<!-- Page specific javascripts-->
<!-- Data table plugin-->
<script type="text/javascript" src="{{ asset('adminPanel/js/plugins/dataTables.bootstrap.min.js')}}"></script>
<script type="text/javascript">
    $('#sampleTable').DataTable();
</script>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript" src="https://rawgit.com/RobinHerbots/jquery.inputmask/3.x/dist/jquery.inputmask.bundle.js"></script>

<script>
//input mask bundle ip address
$('#ipaddress').inputmask({
    alias: "ip",
    greedy: false //The initial mask shown will be "" instead of "-____".
});
</script>

<style>
    em{color: red;}
</style>
<!-- Bet History content end -->
@endsection