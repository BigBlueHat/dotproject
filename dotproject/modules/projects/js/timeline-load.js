var tl;

function timelineLoadEvents(transport, json)
{
	var eventXML = transport.responseXML;
	var eventSource = new Timeline.DefaultEventSource();
	eventSource.loadXML(eventXML, location.pathname);

	
	var bandInfos = [
    Timeline.createBandInfo({
		eventSource:	eventSource,
        width:          "80%", 
        intervalUnit:   Timeline.DateTime.WEEK, 
        intervalPixels: 150,
		trackHeight:	1.5
    }),
    Timeline.createBandInfo({
		showEventText:	false,
		trackHeight:	0.3,
		trackGap:		0.1,
        width:          "20%", 
        intervalUnit:   Timeline.DateTime.MONTH, 
        intervalPixels: 200
    })
  ];

  bandInfos[1].syncWith = 0;
  bandInfos[1].highlight = true;
  bandInfos[1].eventSource = bandInfos[0].eventSource;
  bandInfos[1].eventPainter.setLayout(bandInfos[0].eventPainter.getLayout());

  tl = Timeline.create($("project-timeline"), bandInfos);
}


function timelineOnLoad() {

  var url = location.pathname;
  var projectid = $('project_id').value;

  new Ajax.Request(url, {
		method: 'get',
		parameters: {
			m: "tasks",
			a: "xmllisttasks",
			suppressHeaders: "1",
			project_id: projectid
		},
		onSuccess: timelineLoadEvents
	});
}

var resizeTimerID = null;
function timelineOnResize() {
    if (resizeTimerID == null) {
        resizeTimerID = window.setTimeout(function() {
            resizeTimerID = null;
            tl.layout();
        }, 500);
    }
}

Event.observe(window, 'load', timelineOnLoad);
Event.observe(window, 'resize', timelineOnResize);
