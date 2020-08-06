@extends('adminPanel.layout.app')
@section('content')
<!-- Bet History content start -->
<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="app-menu__icon fa fa-th-list"></i> {{__('adminPanel.stock')}}</h1>
            <p>{{__('adminPanel.dashboardDesc')}}</p>
        </div>
        <ul class="app-breadcrumb breadcrumb side">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item active"><a href="{!!route('vStock')!!}">{{__('adminPanel.stock')}}</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <div class="tile-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="sampleTable">
                            <thead>
                                <tr>
                                    <th>{{ __('adminPanel.stockID') }}</th>
                                    <th>{{ __('adminPanel.portalProviderID') }}</th>
                                    <th>{{ __('adminPanel.portalProviderName') }}</th>
                                    <th>{{ __('adminPanel.stockName') }}</th>
                                    <th>{{ __('adminPanel.totalNumberOfGames') }}</th>
                                    <th>{{ __('adminPanel.totalNumberOfBets') }}</th>
                                    <th>{{ __('adminPanel.totalBetAmount') }}</th>
                                    <th>{{ __('adminPanel.totalRollingAmount') }}</th>
                                    <th>{{ __('adminPanel.ReferenceURL') }}</th>
                                    <th>{{ __('adminPanel.closeDays') }}</th>
                                    <th>{{ __('adminPanel.openTimeRange') }}</th>
                                    <th>{{ __('adminPanel.country') }}</th>
                                    <th>{{ __('adminPanel.isActive') }}</th>
                                    <th> {{__('adminPanel.moreStockInfo')}} </th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($stockData))
                                    @foreach ($stockData as $stock)
                                        <tr>
                                            <th>{{ $stock['stockID'] }}</th>
                                            <th>{{ $stock['portalProviderUUID'] }}</th>
                                            <th>{{ $stock['portalProviderName'] }}</th>
                                            <th>{{ $stock['stockName'] }}</th>
                                            <th>{{ $stock['totalGames'] }}</th>
                                            <th>{{ $stock['totalBets'] }}</th>
                                            <th>{{ $stock['totalBetAmount'] }}</th>
                                            <th>{{ $stock['totalRollingAmount'] }}</th>
                                            <th><a href="#" onclick="pupUpNewPage({url:'{{$stock['referenceURL']}}'})">{{$stock['referenceURL']}}</a></th>
                                            <th>{{ closeDayConverter($stock['closeDays'],getDay())}}</th>
                                            <th>{{ $stock['openTimeRange'] }}</th>
                                            <th>{{ $stock['category'] }}</th>
                                            <th><span class="badge {{ $stock['isActive'] == 'active' ? 'badge-success' : 'badge-danger'}}" style="padding: 4px; font-size: 13px;">{{ $stock['isActive'] }}</span></th>
                                        <td><a href="/admin/gameHistory?stockID={{ $stock['stockID'] }}&portalProviderID={{ $stock['portalProviderUUID'] }}" class='btn btn-primary'>{{__('adminPanel.moreStockInfo')}}</a></td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<!-- Page specific javascripts-->
<!-- Data table plugin-->
<script type="text/javascript" src="{{ asset('adminPanel/js/plugins/dataTables.bootstrap.min.js')}}"></script>
<script type="text/javascript">
    function pupUpNewPage({url}){
        return window.open(url)
    }
    $(document).ready(function(){
        var sampleTable = $('#sampleTable').DataTable({
            responsive: true,
            dom: "<'row'<'col-sm-5'l><'col-sm-7'f>>"+"<'row'<'col-sm-12'tr>>"+"<'row'<'col-sm-5'i><'col-sm-7'p>>",
            language:{
                search: "_INPUT_",
                searchPlaceholder: "{{__('adminPanel.search')}}"
            }
        });
        $('.dataTables_filter').prepend('<label>').children().first().append('@lang('adminPanel.searchByFieldName'):<select class="form-control form-control-sm selectable"><option>@lang('adminPanel.all')</option><option value="0">@lang('adminPanel.stockID')</option><option value="1">@lang('adminPanel.portalProviderID')</option><option value="2">@lang('adminPanel.portalProviderName')</option><option value="3">@lang('adminPanel.stockName')</option><option value="4">@lang('adminPanel.totalNumberOfGames')</option><option value="5">@lang('adminPanel.totalNumberOfBets')</option><option value="8">@lang('adminPanel.ReferenceURL')</option><option value="9">@lang('adminPanel.closeDays')</option><option value="10">@lang('adminPanel.openTimeRange')</option><option value="11">@lang('adminPanel.country')</option><option value="12">@lang('adminPanel.isActive')</option></select>').dataTableFilter(sampleTable);
        $('.dataTables_filter').append('<br><button class="btn" onClick="window.location.reload();"><i class="fa fa-refresh" aria-hidden="true"></i>@lang('adminPanel.refresh')</button>').dataTableFilter(table);

    });
</script>
<!-- Bet History content end -->
@endsection
