<script type="text/javascript">
    function getRandomeTime(){
        var d = new Date();
        return d.getHours() + '' + d.getMinutes() + '' + d.getSeconds() + '' + d.getMilliseconds();
    }
</script>
<div id="dashboard">
    <div class="row">
        <div class="col-lg-12">
            <div class="panel" id="calendar">
                <form class="form-inline" name="reportdate_form" id="reportdate_form" method="post" action="#">
                    <div class="btn-group">
                        {* day|today *}
                        <button class="btn btn-default submitDateDayToday {if $piwik_reportdate == 'day|today'}active{/if}" name="submitDateDayToday" type="button">{l s='Today' mod='piwikdashboard'}</button>
                        {* day| *}
                        <button class="btn btn-default submitDateDayYesterday {if $piwik_reportdate == 'day|yesterday'}active{/if}" name="submitDateDayYesterday" type="button">{l s='Yesterday' mod='piwikdashboard'}</button>
                        {* year|today *}
                        <button class="btn btn-default submitDateYearToday {if $piwik_reportdate == 'year|today'}active{/if}" name="submitDateYearToday" type="button">{l s='Current Year' mod='piwikdashboard'}</button>
                        {* month|today *}
                        <button class="btn btn-default submitDateMonthToday {if $piwik_reportdate == 'month|today'}active{/if}" name="submitDateMonthToday" type="button">{l s='Current Month' mod='piwikdashboard'}</button>
                        {* week|today *}
                        <button class="btn btn-default submitDateWeekToday {if $piwik_reportdate == 'week|today'}active{/if}" name="submitDateWeekToday" type="button">{l s='Current Week' mod='piwikdashboard'}</button>
                        {* range|last30 *}
                        <button class="btn btn-default submitDateRangeLast30 {if $piwik_reportdate == 'range|last30'}active{/if}" name="submitDateRangeLast30" type="button">{l s='Last 30 days (including today)' mod='piwikdashboard'}</button>
                        {* range|last7 *}
                        <button class="btn btn-default submitDateRangeLast7 {if $piwik_reportdate == 'range|last7'}active{/if}" name="submitDateRangeLast7" type="button">{l s='Last 7 days (including today)' mod='piwikdashboard'}</button>
                        {* range|previous30 *}
                        <button class="btn btn-default submitDateRangePrevious30 {if $piwik_reportdate == 'range|previous30'}active{/if}" name="submitDateRangePrevious30" type="button">{l s='Previous 30 days (not including today)' mod='piwikdashboard'}</button>
                        {* range|previous7 *}
                        <button class="btn btn-default submitDateRangePrevious7 {if $piwik_reportdate == 'range|previous7'}active{/if}" name="submitDateRangePrevious7" type="button">{l s='Previous 7 days (not including today)' mod='piwikdashboard'}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-lg-3">
            
            <section class="panel widget">
                <script type="text/javascript">
                    window.piwikLiveOnlineVisitorsTimer = null;
                $(document).ready(function() {
                    piwikLiveOnlineVisitors();
                });

                function piwikLiveOnlineVisitors(){
                    var d = new Date();
                    var dashboardhLink = '{$piwik_dashboard_controller_link}&ajax=1&lastMinutes=5&action=getLiveCounters';
                    $.ajax({
                        type: "GET",
                        url: dashboardhLink + "&_rt="+getRandomeTime(),
                        dataType: 'json',
                        async: true,
                        headers: { {$AuthorizationHeaders} },
                        success: function (data, textStatus, jqXHR){

                            $('#online_actions').html((data[0].actions === undefined || data[0].actions === false ? 0 : data[0].actions) + ' {l s='actions' mod='piwikdashboard'}');
                            $('#online_visitor').html((data[0].visitors === undefined || data[0].visitors === false ? 0 : data[0].visitors) + ' {l s='visits' mod='piwikdashboard'}');
                            $('#online_visitor_title').attr('title',(data[0].visitors === undefined || data[0].visitors === false ? 0 : data[0].visitors) + ' {l s='visitors' mod='piwikdashboard'}');
                            $('#online_visitor_large').html(data[0].visitors === undefined || data[0].visitors === false ? 0 : data[0].visitors);

                            window.piwikLiveOnlineVisitorsTimer = window.setTimeout( piwikLiveOnlineVisitors , 5000);
                        },
                    });
                }
                </script>
                <div class="panel-heading">
                    <i class="icon-time"></i> {l s='Real Time Visitor Count' mod='piwikdashboard'}
                    <span class="panel-heading-action">
			<a title="{l s='Reload' mod='piwikdashboard'}" onclick="window.clearTimeout(window.piwikLiveOnlineVisitorsTimer); piwikLiveOnlineVisitors(); return false;" href="#" class="list-toolbar-btn">
				<i class="process-icon-refresh"></i>
			</a>
                    </span>
                </div>
                <section class="text-center" id="dash_live">
                    <div id="online_visitor_title" title="0 {l s='visitors' mod='piwikdashboard'}" class="simple-realtime-visitor-counter">
                        <div id="online_visitor_large">0</div>
                    </div>
                    <div class="simple-realtime-elaboration">
                        <span class="simple-realtime-metric" id="online_visitor">0 {l s='visits' mod='piwikdashboard'}</span>
                        {l s='and' mod='piwikdashboard'}
                        <span class="simple-realtime-metric" id="online_actions">0 {l s='actions' mod='piwikdashboard'}</span>
                        {l s='in the last' mod='piwikdashboard'}
                        <span class="simple-realtime-metric">5 {l s='minutes' mod='piwikdashboard'}</span>
                    </div>
                </section>
            </section>
                
                
            <section class="panel widget">
                <div class="panel-heading">
                    <i class="icon-time"></i> {l s='Visitors in Real-time' mod='piwikdashboard'}
                </div>
                <section class="" id="dash_live_last10">
                    
                    <script type="text/javascript">
                        $(document).ready(function () {
                            piwikLast24HoursVisitsDetails();
                            piwikLast30MinutesVisitsDetails();
                            piwikLastVisitsDetails();
                        });
                        function piwikLastVisitsDetails(){
                            var dashboardhLink = '{$piwik_dashboard_controller_link}&ajax=1&action=getLastVisitsDetails&period={$piwik_period}&date={$piwik_date}&countVisitorsToFetch=10';
                            $.ajax({
                                type: "GET",
                                url: dashboardhLink + "&_rt="+getRandomeTime(),
                                dataType: 'json',
                                async: true,
                                headers: { {$AuthorizationHeaders} },
                                success: function (data, textStatus, jqXHR){
                                    var html ='';
                                    var __actionCounter = 0;
                                    if (data !== 'false' && data !== false) {
                                        for (var i = 0; i < data.length; i++) {
                                            
                                            
                                            var actionsHtml = '';
                                            
                                            for (var actionI = 0; actionI < data[i].actionDetails.length; actionI++) {
                                                
                                                if (data[i].actionDetails[actionI].type === 'search') {
                                                    actionsHtml += '<a target="_blank" href="#" style="padding-left: 2px; padding-right: 2px;"><img title="{l s='Site Search:' mod='piwikdashboard'} ' + data[i].actionDetails[actionI].siteSearchKeyword + '\n - ' + data[i].actionDetails[actionI].serverTimePretty + '" src="{$protocol}{$piwik_host}' + data[i].actionDetails[actionI].icon + '"/></a>';
                                                } else if (data[i].actionDetails[actionI].type === 'action') {
                                                    actionsHtml += '<a target="_blank" href="' + data[i].actionDetails[actionI].url + '"><img title="' + data[i].actionDetails[actionI].pageTitle + '\n - ' + data[i].actionDetails[actionI].serverTimePretty + (data[i].actionDetails[actionI].timeSpentPretty !== undefined ? '\n {l s='Time on page:' mod='piwikdashboard'} ' + data[i].actionDetails[actionI].timeSpentPretty :'') + '" src="{$protocol}{$piwik_host}plugins/Live/images/file' + __actionCounter + '.png"/></a>';
                                                    __actionCounter++;
                                                } else if (data[i].actionDetails[actionI].type === 'download') {
                                                    actionsHtml += '<a target="_blank" href="' + data[i].actionDetails[actionI].url + '"><img title="' + (data[i].actionDetails[actionI].pageTitle === null ? data[i].actionDetails[actionI].url : data[i].actionDetails[actionI].pageTitle) + ' - ' + data[i].actionDetails[actionI].serverTimePretty + '" src="{$protocol}{$piwik_host}' + data[i].actionDetails[actionI].icon + '" /></a>';
                                                } else if (data[i].actionDetails[actionI].type === 'outlink') {
                                                    actionsHtml += '<a target="_blank" href="' + data[i].actionDetails[actionI].url + '"><img title="' + (data[i].actionDetails[actionI].pageTitle === null ? data[i].actionDetails[actionI].url : data[i].actionDetails[actionI].pageTitle) + ' - ' + data[i].actionDetails[actionI].serverTimePretty + '" src="{$protocol}{$piwik_host}' + data[i].actionDetails[actionI].icon + '" /></a>';
                                                } else if (data[i].actionDetails[actionI].type === 'ecommerceAbandonedCart') {
                                                    actionsHtml += '<span style="padding-left: 2px; padding-right: 2px;">';
                                                    actionsHtml += '<img title="{l s='Abandoned Cart' mod='piwikdashboard'}';
                                                    actionsHtml += '\n - {l s='Revenue left in cart:' mod='piwikdashboard'} {$piwik_currency_prefix}' + data[i].actionDetails[actionI].revenue + ' {$piwik_currency_sign}{$piwik_currency_suffix}';
                                                    actionsHtml += '\n - ' + data[i].actionDetails[actionI].serverTimePretty + '\n ';
                                                    if(data[i].actionDetails[actionI].itemDetails.length > 0){
                                                        var actionDetailsI = 0;
                                                        for (actionDetailsI = 0; actionDetailsI < data[i].actionDetails[actionI].itemDetails.length; actionDetailsI++) {
                                                            actionsHtml += '\n # ' + data[i].actionDetails[actionI].itemDetails[actionDetailsI].itemSKU + ': ';
                                                            actionsHtml += data[i].actionDetails[actionI].itemDetails[actionDetailsI].itemName;
                                                            actionsHtml += ' (' + data[i].actionDetails[actionI].itemDetails[actionDetailsI].itemCategory + '), ';
                                                            actionsHtml += '{l s='Quantity:' mod='piwikdashboard'} ' + data[i].actionDetails[actionI].itemDetails[actionDetailsI].quantity + ', ';
                                                            actionsHtml += '{l s='Price:' mod='piwikdashboard'} {$piwik_currency_prefix}' + data[i].actionDetails[actionI].itemDetails[actionDetailsI].price + ' {$piwik_currency_sign}{$piwik_currency_suffix}';
                                                        }
                                                    }
                                                    actionsHtml += '" ';
                                                    actionsHtml += 'src="{$protocol}{$piwik_host}' + data[i].actionDetails[actionI].icon + '" /></span>';
                                                } else if (data[i].actionDetails[actionI].type === 'ecommerceOrder') {
                                                    actionsHtml += '<span style="padding-left: 2px; padding-right: 2px;">';
                                                    actionsHtml += '<img title="{l s='Ecommerce order' mod='piwikdashboard'}';
                                                    actionsHtml += '\n - {l s='Revenue:' mod='piwikdashboard'} {$piwik_currency_prefix}' + data[i].actionDetails[actionI].revenue + '{$piwik_currency_sign}{$piwik_currency_suffix}';
                                                    actionsHtml += '\n - ' + data[i].actionDetails[actionI].serverTimePretty + '\n ';
                                                    if(data[i].actionDetails[actionI].itemDetails.length > 0){
                                                        var actionDetailsI = 0;
                                                        for (actionDetailsI = 0; actionDetailsI < data[i].actionDetails[actionI].itemDetails.length; actionDetailsI++) {
                                                            actionsHtml += '\n # ' + data[i].actionDetails[actionI].itemDetails[actionDetailsI].itemSKU + ': ';
                                                            actionsHtml += data[i].actionDetails[actionI].itemDetails[actionDetailsI].itemName;
                                                            actionsHtml += ' (' + data[i].actionDetails[actionI].itemDetails[actionDetailsI].itemCategory + '), ';
                                                            actionsHtml += '{l s='Quantity:' mod='piwikdashboard'} ' + data[i].actionDetails[actionI].itemDetails[actionDetailsI].quantity + ', ';
                                                            actionsHtml += '{l s='Price:' mod='piwikdashboard'} {$piwik_currency_prefix}' + data[i].actionDetails[actionI].itemDetails[actionDetailsI].price + '{$piwik_currency_sign}{$piwik_currency_suffix}';
                                                        }
                                                    }
                                                    actionsHtml += '" ';
                                                    actionsHtml += 'src="{$protocol}{$piwik_host}' + data[i].actionDetails[actionI].icon + '" /> {l s='Revenue:' mod='piwikdashboard'} {$piwik_currency_prefix}' + data[i].actionDetails[actionI].revenue + ' {$piwik_currency_sign}{$piwik_currency_suffix}</span>';
                                                } else if(data[i].actionDetails[actionI].type === 'event') {
                                                    actionsHtml += '<a target="_blank" href="' + data[i].actionDetails[actionI].url + '">';
                                                    actionsHtml += '<img title="' + data[i].actionDetails[actionI].eventCategory + ' - ' + data[i].actionDetails[actionI].eventAction + ' - ' + data[i].actionDetails[actionI].eventName + '" src="{$protocol}{$piwik_host}' + data[i].actionDetails[actionI].icon + '">';
                                                    actionsHtml += '</a>';
                                                } else {
                                                    // new type or just some thing i over looked ;\
                                                    console.log(data[i].actionDetails[actionI]);
                                                }

                                                if(__actionCounter === 10)
                                                    __actionCounter = 0;

                                            }
                                            
                                            
                                            var innerHtml = '<li id="idvisit_' + data[i].idvisit + '">';
                                                innerHtml += '<div title="' + data[i].actions + ' {l s='Actions' mod='piwikdashboard'}">';
                                                innerHtml += '<span id="serverDatePretty">' + data[i].serverDatePretty + '</span> - <span id="serverTimePretty">' + data[i].serverTimePretty + ' <em id="visitDurationPretty">(' + data[i].visitDurationPretty + ')</em></span>';
                                                innerHtml += '<br>';
                                                innerHtml += '<a onclick="piwikViewVisitorProfile(\'' + data[i].visitorId + '\')" title="View visitor profile ' + (data[i].userId === undefined || data[i].userId === false ? data[i].visitorId : data[i].userId) + '">';
                                                innerHtml += '<img src="{$protocol}{$piwik_host}plugins/Live/images/visitorProfileLaunch.png">';
                                                innerHtml += '</a>' + (data[i].userId === undefined || data[i].userId === false || data[i].userId === null ? '':'<a target="_blank" href="{$piwik_customers_controller_link}&id_customer=' + data[i].userId + '&viewcustomer" title="View customer ' + data[i].userId + '">' + data[i].userId + '</a>');
                                                innerHtml += '&nbsp;<img title="' + data[i].country + ', {l s='Provider' mod='piwikdashboard'} ' + data[i].providerName + '" src="{$protocol}{$piwik_host}' + data[i].countryFlag + '">';
                                                innerHtml += '&nbsp;<img title="' + data[i].browser + ', {l s='Plugins:' mod='piwikdashboard'} ' + data[i].plugins + '" src="{$protocol}{$piwik_host}' + data[i].browserIcon + '">';
                                                innerHtml += '&nbsp;<img title="' + data[i].operatingSystem + ', ' + data[i].resolution + '" src="{$protocol}{$piwik_host}' + data[i].operatingSystemIcon + '">';
                                                if (typeof data[i].visitorTypeIcon  !== 'undefined' && data[i].visitorTypeIcon !== null) {
                                                    innerHtml += '&nbsp;<img title="' + data[i].visitorType + '" src="{$protocol}{$piwik_host}' + data[i].visitorTypeIcon + '">';
                                                }
                                                innerHtml += '<br><span title="{l s='Visitor ID:' mod='piwikdashboard'} ' + data[i].visitorId + '">{l s='IP:' mod='piwikdashboard'} ' + data[i].visitIp + (data[i].visitIpHost !== undefined && data[i].visitIpHost !==  "" ? ' ('+data[i].visitIpHost+')':'' ) + '</span>';
                                                
                                                if(data[i].referrerType === 'website'){
                                                    innerHtml += '<br><span>{l s='from' mod='piwikdashboard'} <a target="_blank" rel="noreferrer" href="' + data[i].referrerUrl + '">' + data[i].referrerName + '</a></span></div>';
                                                } else {
                                                    innerHtml += '<br><span>' + data[i].referrerTypeName + '</span></div>';
                                                }
                                                
                                                innerHtml += '<div id="' + data[i].idvisit + '_actions">';
                                                innerHtml += '<span title="' + data[i].actions + ' {l s='Actions' mod='piwikdashboard'}">{l s='Actions:' mod='piwikdashboard'}</span><br>' + actionsHtml +'</div></li>';
                                                
                                            html += innerHtml;
                                        }
                                    }
                                    $("#last10visitors").html(html);
                                    setTimeout( piwikLastVisitsDetails , 30000);
                                },
                            });
                        }
                        function piwikLast24HoursVisitsDetails(){
                            var d = new Date();
                            var dashboardhLink = '{$piwik_dashboard_controller_link}&ajax=1&lastMinutes=1440&action=getLiveCounters';
                            $.ajax({
                                type: "GET",
                                url: dashboardhLink + "&_rt="+d.getHours() + '' + d.getMinutes() + '' + d.getSeconds() + '' + d.getMilliseconds(),
                                dataType: 'json',
                                async: true,
                                headers: { {$AuthorizationHeaders} },
                                success: function (data, textStatus, jqXHR){

                                    $('#last24hActions').html((data[0].actions === undefined || data[0].actions === false ? 0 : data[0].actions));
                                    $('#last24hVisits').html((data[0].visits === undefined || data[0].visits === false ? 0 : data[0].visits));
                                    $('#last24hConverted').html((data[0].visitsConverted === undefined || data[0].visitsConverted === false ? 0 : data[0].visitsConverted));
                                    
                                    setTimeout( piwikLast24HoursVisitsDetails , 30000);
                                },
                            });
                        }
                        function piwikLast30MinutesVisitsDetails(){
                            var d = new Date();
                            var dashboardhLink = '{$piwik_dashboard_controller_link}&ajax=1&lastMinutes=30&action=getLiveCounters';
                            $.ajax({
                                type: "GET",
                                url: dashboardhLink + "&_rt="+d.getHours() + '' + d.getMinutes() + '' + d.getSeconds() + '' + d.getMilliseconds(),
                                dataType: 'json',
                                async: true,
                                headers: { {$AuthorizationHeaders} },
                                success: function (data, textStatus, jqXHR){

                                    $('#last30mActions').html((data[0].actions === undefined || data[0].actions === false ? 0 : data[0].actions));
                                    $('#last30mVisits').html((data[0].visits === undefined || data[0].visits === false ? 0 : data[0].visits));
                                    $('#last30mConverted').html((data[0].visitsConverted === undefined || data[0].visitsConverted === false ? 0 : data[0].visitsConverted));
                                    
                                    setTimeout( piwikLast30MinutesVisitsDetails , 30000);
                                },
                            });
                        }
                    </script>
                    
                    <table class="table data_table">
                        <thead>
                            <tr>
                                <th class="text-left">{l s='Date' mod='piwikdashboard'}</th>
                                <th class="text-center">{l s='Visits' mod='piwikdashboard'}</th>
                                <th class="text-center">{l s='Actions' mod='piwikdashboard'}</th>
                                <th class="text-center">{l s='Converted' mod='piwikdashboard'}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-left">{l s='Last 24 hours' mod='piwikdashboard'}</td>
                                <td class="text-center" id="last24hVisits">0</td>
                                <td class="text-center" id="last24hActions">0</td>
                                <td class="text-center" id="last24hConverted">0</td>
                            </tr>
                            <tr>
                                <td class="text-left">{l s='Last 30 minutes' mod='piwikdashboard'}</td>
                                <td class="text-center" id="last30mVisits">0</td>
                                <td class="text-center" id="last30mActions">0</td>
                                <td class="text-center" id="last30mConverted">0</td>
                            </tr>
                        </tbody>
                    </table>
                    <p><br></p>
                    <ul class="data_list" id="last10visitors"></ul>
                    
                </section>
            </section>
        </div>
        <div class="col-md-12 col-lg-6">

            <section class="panel widget">
                <div class="panel-heading">
                    <i class="icon-time"></i>
                    {l s='Visits by Local Time' mod='piwikdashboard'}:
                    {if $piwik_reportdate == 'day|today'}
                        {l s='Today' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'day|yesterday'}
                        {l s='Yesterday' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'year|today'}
                        {l s='Current Year' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'month|today'}
                        {l s='Current Month' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'week|today'}
                        {l s='Current Week' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'range|last30'}
                        {l s='Last 30 days' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'range|last7'}
                        {l s='Last 7 days' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'range|previous30'}
                        {l s='Previous 30 days' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'range|previous7'}
                        {l s='Previous 7 days' mod='piwikdashboard'}
                    {/if}
                    <span class="panel-heading-action">
			<a title="{l s='Reload' mod='piwikdashboard'}" onclick="$('#dash_visit_per_local_time_img').attr('src', ''); setTimeout( setVisitsPerLocalTimeImg , 1000); return false;" href="#" class="list-toolbar-btn">
				<i class="process-icon-refresh"></i>
			</a>
                    </span>
                </div>
                <section class="" id="dash_visit_per_local_time">
                    <script type="text/javascript">
                        $(document).ready(function () {
                            $("#dash_visit_per_local_time_img").attr('src', '');
                            $(window).on('resize', function () {
                                $("#dash_visit_per_local_time_img").attr('src', '');
                                setTimeout( setVisitsPerLocalTimeImg , 1000);
                            });
                            setTimeout( setVisitsPerLocalTimeImg , 1000);
                        });
                        function setVisitsPerLocalTimeImg(){
                            var imageUrlPerLocal = "{$protocol}{$piwik_host}index.php";
                            imageUrlPerLocal += "?module=API&method=ImageGraph.get&idSite={$piwik_siteid}";
                            imageUrlPerLocal += "&apiModule=VisitTime&apiAction=getVisitInformationPerLocalTime";
                            imageUrlPerLocal += "&period={$piwik_period}&date={$piwik_date}";
                            imageUrlPerLocal += "&token_auth={$piwik_token}";
                            imageUrlPerLocal += "&language={$iso_code}";
                            imageUrlPerLocal += "&graphType=verticalBar";
                            $("#dash_visit_per_local_time_img").attr('src', imageUrlPerLocal + '&width=' + $("#dash_visit_per_local_time").width() + '&height=200&_rt='+getRandomeTime());
                        }
                    </script>
                    <img id="dash_visit_per_local_time_img" title="{l s='Visits by Local Time' mod='piwikdashboard'}" />
                </section>
            </section>

            <section class="panel widget">
                <div class="panel-heading">
                    <i class="icon-time"></i> {l s='Length of Visits' mod='piwikdashboard'}
                    <span class="panel-heading-action">
			<a title="{l s='Reload' mod='piwikdashboard'}" onclick="$('#dash_visits_per_visit_duration_img').attr('src', ''); setTimeout( setVisitsPerDurationImg , 1000); return false;" href="#" class="list-toolbar-btn">
				<i class="process-icon-refresh"></i>
			</a>
                    </span>
                </div>
                <section class="text-center" id="dash_visits_Per_visit_duration">
                    <script type="text/javascript">
                        $(document).ready(function () {
                            $("#dash_visits_per_visit_duration_img").attr('src', '');
                            setTimeout( setVisitsPerDurationImg , 1000);
                        });
                        function setVisitsPerDurationImg(){
                            var imageUrlPerDuration = "{$protocol}{$piwik_host}index.php";
                            imageUrlPerDuration += "?module=API&method=ImageGraph.get&idSite={$piwik_siteid}";
                            imageUrlPerDuration += "&apiModule=VisitorInterest&apiAction=getNumberOfVisitsPerVisitDuration";
                            imageUrlPerDuration += "&period={$piwik_period}&date={$piwik_date}";
                            imageUrlPerDuration += "&token_auth={$piwik_token}";
                            imageUrlPerDuration += "&language={$iso_code}";
                            imageUrlPerDuration += "&graphType=pie";
                            $("#dash_visits_per_visit_duration_img").attr('src', imageUrlPerDuration + '&width=300&height=200&_rt='+getRandomeTime());
                        }
                    </script>
                    <img id="dash_visits_per_visit_duration_img" title="{l s='Visits by Local Time' mod='piwikdashboard'}" />
                </section>
            </section>
        </div>
        <div class="col-md-12 col-lg-3">
            
            <section class="panel widget">
                <div class="panel-heading">
                    <i class="icon-time"></i>
                    {l s='Actions:' mod='piwikdashboard'}
                    {if $piwik_reportdate == 'day|today'}
                        {l s='Today' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'day|yesterday'}
                        {l s='Yesterday' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'year|today'}
                        {l s='Current Year' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'month|today'}
                        {l s='Current Month' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'week|today'}
                        {l s='Current Week' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'range|last30'}
                        {l s='Last 30 days' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'range|last7'}
                        {l s='Last 7 days' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'range|previous30'}
                        {l s='Previous 30 days' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'range|previous7'}
                        {l s='Previous 7 days' mod='piwikdashboard'}
                    {/if}
                </div>
                <section class="" id="dash_live_actions">
                    <script type="text/javascript">
                        $(document).ready(function() {
                            piwikLiveActions();
                        });

                        function piwikLiveActions(){
                            var dashboardhLink = '{$piwik_dashboard_controller_link}&ajax=1&action=getActions&period={$piwik_period}&date={$piwik_date}';
                            $.ajax({
                                type: "GET",
                                url: dashboardhLink + "&_rt="+getRandomeTime(),
                                dataType: 'json',
                                async: true,
                                headers: { {$AuthorizationHeaders} },
                                success: function (data, textStatus, jqXHR){
                                    if (data !== 'false' && data !== false && data.nb_pageviews !== undefined) {
                                        var total_uniq_actions = parseInt(0 + data.nb_uniq_pageviews + data.nb_uniq_downloads + data.nb_uniq_outlinks);
                                        var total_actions = parseInt(data.nb_pageviews + data.nb_downloads + data.nb_outlinks);

                                        $('#total_uniq_actions').html(total_uniq_actions);
                                        $('#total_actions').html(total_actions);
                                        $('#nb_pageviews').html(data.nb_pageviews);
                                        $('#nb_uniq_pageviews').html(data.nb_uniq_pageviews);
                                        $('#nb_downloads').html(data.nb_downloads);
                                        $('#nb_uniq_downloads').html(data.nb_uniq_downloads);
                                        $('#nb_outlinks').html(data.nb_outlinks);
                                        $('#nb_uniq_outlinks').html(data.nb_uniq_outlinks);
                                        $('#nb_searches').html(data.nb_searches);
                                        $('#nb_keywords').html(data.nb_keywords);
                                        $('#avg_time_generation').html(data.avg_time_generation + ' ms');
                                        setTimeout( piwikLiveActions , 30000);
                                    } else {
                                        setTimeout( piwikLiveActions , 10000);
                                    }
                                },
                            });
                        }
                    </script>
                    <ul class="data_list_vertical">
                        <li>
                            <span class="data_label">{l s='Uniq Actions' mod='piwikdashboard'}</span>
                            <span class="data_value size_l">
                                <span id="total_uniq_actions">0</span>
                            </span>
                        </li>
                        <li>
                            <span class="data_label">{l s='Actions' mod='piwikdashboard'}</span>
                            <span class="data_value size_l">
                                <span id="total_actions">0</span>
                            </span>
                        </li>
                    </ul>
                    <ul class="data_list">
                        <li>
                            <span class="data_label">{l s='Page views' mod='piwikdashboard'}</span>
                            <span class="data_value size_md">
                                <span id="nb_pageviews">0</span>
                            </span>
                        </li>
                        <li>
                            <span class="data_label">{l s='Uniq Page views' mod='piwikdashboard'}</span>
                            <span class="data_value size_md">
                                <span id="nb_uniq_pageviews">0</span>
                            </span>
                        </li>
                        <li>
                            <span class="data_label">{l s='Downloads' mod='piwikdashboard'}</span>
                            <span class="data_value size_md">
                                <span id="nb_downloads">0</span>
                            </span>
                        </li>
                        <li>
                            <span class="data_label">{l s='Uniq Downloads' mod='piwikdashboard'}</span>
                            <span class="data_value size_md">
                                <span id="nb_uniq_downloads">0</span>
                            </span>
                        </li>
                        <li>
                            <span class="data_label">{l s='Outlinks' mod='piwikdashboard'}</span>
                            <span class="data_value size_md">
                                <span id="nb_outlinks">0</span>
                            </span>
                        </li>
                        <li>
                            <span class="data_label">{l s='Uniq outlinks' mod='piwikdashboard'}</span>
                            <span class="data_value size_md">
                                <span id="nb_uniq_outlinks">0</span>
                            </span>
                        </li>
                        <li>
                            <span class="data_label">{l s='Searches' mod='piwikdashboard'}</span>
                            <span class="data_value size_md">
                                <span id="nb_searches">0</span>
                            </span>
                        </li>
                        <li>
                            <span class="data_label">{l s='Keywords' mod='piwikdashboard'}</span>
                            <span class="data_value size_md">
                                <span id="nb_keywords">0</span>
                            </span>
                        </li>
                        <li>
                            <span class="data_label">{l s='Avg. Generation time' mod='piwikdashboard'}</span>
                            <span class="data_value size_md">
                                <span id="avg_time_generation">0.000 ms</span>
                            </span>
                        </li>
                    </ul>
                </section>
            </section>
                            
            <section class="panel widget">
                <div class="panel-heading">
                    <i class="icon-time"></i>
                    {l s='Site searches without result' mod='piwikdashboard'}:
                    {if $piwik_reportdate == 'day|today'}
                        {l s='Today' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'day|yesterday'}
                        {l s='Yesterday' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'year|today'}
                        {l s='Current Year' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'month|today'}
                        {l s='Current Month' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'week|today'}
                        {l s='Current Week' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'range|last30'}
                        {l s='Last 30 days' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'range|last7'}
                        {l s='Last 7 days' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'range|previous30'}
                        {l s='Previous 30 days' mod='piwikdashboard'}
                    {elseif $piwik_reportdate == 'range|previous7'}
                        {l s='Previous 7 days' mod='piwikdashboard'}
                    {/if}
                </div>
                <section class="" id="dash_searches_no_result">
                    <script type="text/javascript">
                        $(document).ready(function () { 
                            piwikGetSiteSearchNoResultKeywords();
                        });
                        function piwikGetSiteSearchNoResultKeywords(){
                            var dashboardhLink = '{$piwik_dashboard_controller_link}&ajax=1&action=getsitesearchnoresultkeywords&period={$piwik_period}&date={$piwik_date}';
                            $.ajax({
                                type: "GET",
                                url: dashboardhLink+"&_rt="+getRandomeTime(),
                                dataType: 'json',
                                async: true,
                                headers: { {$AuthorizationHeaders} },
                                success: function (data, textStatus, jqXHR){
                                    if (data !== 'false' && data !== false) {
                                        var html = "<thead><tr><th class=\"text-left\">";
                                        html += "{l s='Word' mod='piwikdashboard'}";
                                        html += "</th><th class=\"text-center\">";
                                        html += "{l s='Searches' mod='piwikdashboard'}</th>";
                                        html += "</th><th class=\"text-center\">";
                                        html += "{l s='% Search Exits' mod='piwikdashboard'}";
                                        html += "</tr></thead><tbody>";
                                        if (data.length > 0) {
                                            for (var i = 0; i < data.length; i++) {
                                                html += "<tr>";
                                                html += "<td class=\"text-left\">"+data[i].label+"</td>";
                                                html += "<td class=\"text-center\">"+data[i].nb_hits+"</td>";
                                                html += "<td class=\"text-center\">"+data[i].exit_rate+"</td>";
                                                html += "</tr>";
                                            }
                                        } else {
                                            html += "<tr>";
                                            html += "<td class=\"text-left\" colspan='3'>{l s='No results' mod='piwikdashboard'}</td>";
                                            html += "</tr>";
                                        }
                                        html += "</tbody>";
                                        $("#table_searches_no_result").html(html);
                                    }
                                }
                            });
                            {*
                             we set reload for no result searches to 2.5 min.
                            *}
                            setTimeout( piwikGetSiteSearchNoResultKeywords , 150000);
                        }
                    </script>
                    <table class="table data_table" id="table_searches_no_result">
                        <thead>
                            <tr>
                                <th class="text-left">{l s='Word' mod='piwikdashboard'}</th>
                                <th class="text-center">{l s='Searches' mod='piwikdashboard'}</th>
                                <th class="text-center">{l s='% Search Exits' mod='piwikdashboard'}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td class="text-center" colspan='3'>{l s='No results' mod='piwikdashboard'}</td></tr>
                        </tbody>
                    </table>
                </section>
            </section>
        </div>
    </div>
</div>