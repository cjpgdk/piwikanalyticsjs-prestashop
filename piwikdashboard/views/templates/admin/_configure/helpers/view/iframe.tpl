<script type="text/javascript">
  function WidgetizeiframeDashboardLoaded() {
      var w = $('#content').width();
      var h = $('body').height();
      $('#WidgetizeiframeDashboard').width('100%');
      $('#WidgetizeiframeDashboard').height(h);
  }
</script>
<iframe id="WidgetizeiframeDashboard"
        onload="WidgetizeiframeDashboardLoaded();"
        src="{$protocol}{$piwik_http_auth}{$piwik_host}index.php?module=Widgetize&action=iframe&moduleToWidgetize=Dashboard&actionToWidgetize=index&idSite={$piwik_siteid}&period={$piwik_period}&token_auth={$piwik_token}&language={$iso_code}&date={$piwik_date}"
        frameborder="0"
        marginheight="0"
        marginwidth="0"
        width="100%"
        height="550px"></iframe>