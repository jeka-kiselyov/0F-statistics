<ul class="breadcrumb">
  <li><a href="{$settings->site_path}/admin/">Admin Panel</a> </li>
  <li class="active">Dashboard</li>
</ul>

{if $stats}
<div id="chart_div" style="height: 400px;"></div>

{literal}
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">

      // Load the Visualization API and the piechart package.
    google.load('visualization', '1', {packages: ['annotatedtimeline']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawChart() {
        var data = new google.visualization.DataTable();
    		data.addColumn('date', 'Date');
    		data.addColumn('number', 'Registrations');

        {/literal}
    		data.addRows([
    			{foreach from=$stats item=s}
      	    [new Date({$s.time}000), {$s.registrations}],
          {/foreach}
    		]);
    		{literal}

  		  var annotatedtimeline = new google.visualization.AnnotatedTimeLine(document.getElementById('chart_div'));
  		  annotatedtimeline.draw(data, {'displayAnnotations': true, 'displayRangeSelector': false, 'displayZoomButtons': false, 'fill': 10}, "100%", "100%");
      }
    </script>
{/literal}
{/if}


<div style="height: 50px;"></div>