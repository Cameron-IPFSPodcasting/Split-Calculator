<?php
//CORS annoys the crap out of me, so only using PHP to get the value blocks from podcastindex.org.
if(isset($_GET['feed'])){ $feedGuid=$_GET['feed']; }
else{ $feedGuid='917393e3-1b1e-5cef-ace4-edaa54e1f810'; }  // Default to Podcasting 2.0 Feed
$feedData = @file_get_contents('https://podcastindex.org/api/podcasts/byguid?guid='.$feedGuid); $tmp=json_decode($feedData);
$episodeData = @file_get_contents('https://podcastindex.org/api/episodes/byfeedid?id='.$tmp->feed->id);
//Another PHP snippet below loads the results into a jQuery object...
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="initial-scale = 1.0,maximum-scale = 1.0" />
  <title>Split Calculator</title>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
  <style>
    body { font-family: Helvetica, Arial; font-size: 0.75em; }
    form { width: fit-content; margin: auto; }
    label { margin: 5px; }
    table th, table td { text-align: center; }
    table td.left { text-align: left; }
    table th.boost, table td.boost { background-color: lightblue; }
    input[id$="-split"], input[id$="-start"], input[id$="-duration"], input[id$="-remotestart"], input[id$="-remotepct"] { width: 30px; text-align: center; }
    span#podcastTitle { color: crimson; }
    div#ctms, div#itms { color: #999; }
    select#episode { font-size: 1em; font-weight: bold; border: none; background: #eee; border: 1px solid gray; border-radius: 5px; color: crimson; }
    div.boostcfg { font-size: 1.33em; font-weight: bold; background-color: lightblue; padding: 5px; border-radius: 5px; }
    input#boost { font-size: 1em; font-weight: bold; width: 100px; text-align: center; }
    div#timeSlide { width: calc(100% - 110px); display: inline-flex; }
    #custom-handle { width: 3em; height: 1.6em; top: 50%; margin-top: -.8em; text-align: center; line-height: 1.6em; font-size: 0.75em; font-weight: bold; }
    div#channel, div#item, div#vts { background-color: lightgray; padding: 5px; border-radius: 10px; }
    h3 { margin-bottom: 0; }
    i.pi { font-size: 16pt; color: crimson; }
    input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; }
  </style>
  <link rel="stylesheet" href="https://podcastfont.com/releases/202201131704/css/PodcastFont.css" integrity="sha384-GXfV9/rBA6iCRFCngL3/BZ6nAiPh6LUJVawef09rA6mrpKPMe41y054gBT0oamrA" crossorigin="anonymous" />
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js" integrity="sha512-0bEtK0USNd96MnO4XhH8jhv3nyRF0eK87pJke6pkYf3cM0uDIhNJy9ltuzqgypoIFXw3JSuiy04tVk4AjpZdZw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script type="text/javascript">
    (function ($) {
      //jQuery objects for Feed/Episodes (loaded via PHP)
      var feedData=<?php echo $feedData ?>;
      var episodeData=<?php echo $episodeData ?>;

      $(document).ready(function(){
        //Get url variables
        let searchParams = new URLSearchParams(window.location.search);
        if(searchParams.has('sats')){ sats=posInt(searchParams.get('sats')); }
        else{ sats=10000; }
        $('input#boost').val(sats);
        var timesec=duration=-1;
        if(searchParams.has('sec')){ timesec=posInt(searchParams.get('sec')); }

        //Setup the time slider
        $('div#timeSlide').slider({
          create: function() { $('#custom-handle').text( $(this).slider('value') ); },
          slide: function( event, ui ) { $('#custom-handle').text( ui.value ); },
        });

        $('span#podcastTitle').html(feedData['feed']['title']);
        $('div#ctms').html('Type='+feedData['feed']['value']['model']['type']+' &nbsp; Method='+feedData['feed']['value']['model']['method']+' &nbsp; Suggested='+feedData['feed']['value']['model']['suggested']);

        // Populate Channel Level Splits
        var chval='';
        chval+='<table>';
        chval+='<tr><th class="boost">Sats</th><th class="boost">Percent</th><th>Name</th><th>Split</th><th>Fee</th><th></th><th>Notes</th></tr>';
        $.each(feedData['feed']['value']['destinations'], function(index, value) {
          recip = value.address+'|'+value.customKey+'|'+value.customValue;  // Hash to define recipients
          reciphash = recip.hashCode();
          chval+='<tr id="channel-'+reciphash+'">';
          chval+='<td class="boost"><span id="channel-'+reciphash+'-sats"></span></td>';
          chval+='<td class="boost"><span id="channel-'+reciphash+'-pct"></span></td>';
          chval+='<td class="left"><span id="channel-'+reciphash+'-name">'+value.name+'</span></td>';
          chval+='<td><input id="channel-'+reciphash+'-split" type="number" min="0" value="'+posInt(value.split)+'" /></td>';
          if(value.fee==true){ feechk='checked="checked"'; }
          else{ feechk=''; }
          chval+='<td><input id="channel-'+reciphash+'-fee" type="checkbox" '+feechk+' /></td>';
          chval+='<td><i id="channel-'+reciphash+'-delete" class="pi pi-delete"></i></td>';
          chval+='<td class="left"><span id="channel-'+reciphash+'-note"></span></td>';
          chval+='</tr>';
        });
        chval+='</table>';
        $('div#channel').html(chval);

        $('select#episode option').remove();
        var itemGuid='';
        if(searchParams.has('item')){ itemGuid=searchParams.get('item'); }
        $.each(episodeData['items'], function(index, item) {
          if(itemGuid==item.guid){ $('select#episode').append($('<option></option>').text(item.title).val(item.id).attr('selected', 'selected')); }
          else{ $('select#episode').append($('<option></option>').text(item.title).val(item.id)); }
        });
        
        // Populate Item Level Splits
        $('select#episode').change(function() {
          epval=$(this).val();
          $.each(episodeData['items'], function(eindex, item) {
            if(item.id==epval){
              if(typeof item.value!='undefined'){ $('div#itms').html('Type='+item.value.model.type+' &nbsp; Method='+item.value.model.method+' &nbsp; Suggested='+item.value.model.suggested); }
              duration=item.duration;
              if(duration<0){ duration=9999; }
              if(timesec==-1 || timesec>duration){ timesec=Math.floor(Math.random() * duration); }
              $('div#timeSlide').slider('option', 'max', duration);
              $('div#timeSlide').slider('value', timesec);
              $('#custom-handle').text(timesec);

              var evval='';
              evval+='<table>';
              evval+='<tr><th class="boost">Sats</th><th class="boost">Percent</th><th>Name</th><th>Split</th><th>Fee</th></tr>';
              if(typeof item.value!='undefined'){
                $.each(item.value.destinations, function(vindex, value) {
                  recip = value.address+'|'+value.customKey+'|'+value.customValue;  // Hash to define recipients
                  reciphash = recip.hashCode();
                  evval+='<tr id="item-'+reciphash+'">';
                  evval+='<td class="boost"><span id="item-'+reciphash+'-sats"></span></td>';
                  evval+='<td class="boost"><span id="item-'+reciphash+'-pct"></span></td>';
                  evval+='<td class="left"><span id="item-'+reciphash+'-name">'+value.name+'</span></td>';
                  evval+='<td><input id="item-'+reciphash+'-split" type="number" min="0" value="'+posInt(value.split)+'" /></td>';
                  if(value.fee==true){ feechk='checked="checked"'; }
                  else{ feechk=''; }
                  evval+='<td><input id="item-'+reciphash+'-fee" type="checkbox" '+feechk+' /></td>';
                  evval+='<td><i id="item-'+reciphash+'-delete" class="pi pi-delete"></i></td>';
                  evval+='</tr>';
                });
              }
              evval+='</table>';
              $('div#item').html(evval);

              // Populate Value Time Splits
              var remotevts=localvts='';
              var rcnt=lcnt=0;
              remotevts+='Remote Items<table>';
              remotevts+='<tr><th class="boost">Sats</th><th class="boost">Percent</th><th>Start</th><th>Duration</th><th>Remote<br/>Start</th><th>Remote<br/>Percent</th><th>Notes</th></tr>';
              localvts+='Local Items<table>';
              localvts+='<tr><th class="boost">Sats</th><th class="boost">Percent</th><th>Start</th><th>Duration</th><th>Name</th><th>Split</th><th>Notes</th></tr>';
              $.each(item.timesplits, function(vindex, value) {
                if(value.feedGuid==''){  // No feed guid means it must be a local VTS
                  recip = value.address+'|'+value.customKey+'|'+value.customValue;  // Hash to define recipients
                  reciphash = recip.hashCode();
                  localvts+='<tr id="local-'+reciphash+'">';
                  localvts+='<td class="boost"><span id="local-'+reciphash+'-sats"></span></td>';
                  localvts+='<td class="boost"><span id="local-'+reciphash+'-pct"></span></td>';
                  localvts+='<td><input id="local-'+reciphash+'-start" type="number" min="0" value="'+value.startTime+'" /></td>';
                  localvts+='<td><input id="local-'+reciphash+'-duration" type="number" min="0" value="'+value.duration+'" /></td>';
                  localvts+='<td class="left"><span id="local-'+reciphash+'-name">'+value.name+'</span></td>';
                  localvts+='<td><input id="local-'+reciphash+'-split" type="number" min="0" max="100" value="'+posInt(value.split)+'" /></td>';
                  localvts+='<td class="left"><span id="local-'+reciphash+'-note"></span></td>';
                  localvts+='</tr>';
                  lcnt++;
                }
                else{
                  recip = value.feedGuid+'|'+value.itemGuid;  // Hash to index VTS data
                  reciphash = recip.hashCode();
                  remotevts+='<tr id="remote-'+reciphash+'">';
                  remotevts+='<td class="boost"><span id="remote-'+reciphash+'-sats"></span></td>';
                  remotevts+='<td class="boost"><span id="remote-'+reciphash+'-pct"></span></td>';
                  remotevts+='<td><input id="remote-'+reciphash+'-start" type="number" min="0" value="'+value.startTime+'" /></td>';
                  remotevts+='<td><input id="remote-'+reciphash+'-duration" type="number" min="0" value="'+value.duration+'" /></td>';
                  remotevts+='<td><input id="remote-'+reciphash+'-remotestart" type="number" min="0" value="'+value.remoteStartTime+'" /></td>';
                  remotevts+='<td><input id="remote-'+reciphash+'-remotepct" type="number" min="0" max="100" value="'+value.remotePercentage+'" /></td>';
                  remotevts+='<td class="left"><span id="remote-'+reciphash+'-note" feedguid="'+value.feedGuid+'" itemguid="'+value.itemGuid+'"></span></td>';
                  remotevts+='</tr>';
                  rcnt++;
                }
              });
              remotevts+='</table>';
              localvts+='</table>';
              vtstotal='';
              if(rcnt>0){ vtstotal+=remotevts; }
              if(lcnt>0){ vtstotal+=localvts; }
              $('div#vts').html(vtstotal);
            }
          });
          splitCalc();  // Recalc when they change episodes
        });

        $('select#episode').trigger('change');

        // Data from VTS links are processed differently.
        if(searchParams.has('vts')){
          if(itemGuid==''){ $('div#vtsitem').html(''); }  // with no item guid, splits are only at the channel level
          $('div#vtsvts').html('');  // VTS are never processed when linked from a VTS (recursion), but I don't agree.
        }

        //If anything changes, make positive & recalculate.
        $('form').on('change', 'input', function() { 
          $(this).val(posInt($(this).val())); 
          str=$(this).attr('id');
          if((str.substring(0, str.indexOf('-'))=='remote' || str.substring(0, str.indexOf('-'))=='local') && $(this).val()>100){ $(this).val(100); }
          splitCalc(); 
        });
        //Time slider too
        $('div#timeSlide').on('slidechange', function(event, ui) { splitCalc(); });

        //Delete row when delete icon is clicked
        $('form').on('click', 'i[id$=-delete]', function() {
          str=$(this).attr('id');
          hash=str.substring(0, str.lastIndexOf('-'));
          $('table tr#'+hash).remove();
          splitCalc();
        });

      });

      String.prototype.hashCode = function() {
        var hash = 0, i, chr;
        if (this.length === 0) return hash;
        for (i = 0; i < this.length; i++) {
          chr = this.charCodeAt(i);
          hash = ((hash << 5) - hash) + chr;
          hash |= 0; // Convert to 32bit integer
        }
        return hash;
      }

      //Make sure all the values are positive integers
      function posInt(val){
        int=Math.abs(Math.round(val));
        if(!isFinite(int)){ int=0; }
        return int;
      }

      //Reset the form before calculating
      function resetCalc(){
        $('table tr[id^=channel-], table tr[id^=remote-], table tr[id^=local-]').each(function() { 
          $(this).find('input').prop('disabled', false);
          $(this).css('color', ''); 
        });
        $('span[id^=channel-][id$=-note]').each(function() { $(this).html(''); });
        $('td.boost').children('span').each(function() { $(this).html(''); });
      }


      // @@@@@@@@@@@@@@@@@@@@@@@@
      // @@@ Calculate Splits @@@
      // @@@@@@@@@@@@@@@@@@@@@@@@

      function splitCalc(){
        resetCalc();  // Reset Everything

        // Look for Channel Overrides. Disable the Channel split if there's an Item split with the same wallet hash.
        $('input[id^=channel-][id$=-split]').each(function() {
          str=$(this).attr('id');
          hash=str.substring(str.indexOf('-') + 1, str.lastIndexOf('-'));
          if($('#item-'+hash+'-split').length){ 
            $('table tr#channel-'+hash).each(function() { $(this).find('input').prop('disabled', true); });
            $('table tr#channel-'+hash).css('color', '#aaa');
            $('span#channel-'+hash+'-note').html('Item Overide');
          }
        });

        // Add up all (enabled) shares
        sharetotal=0;
        $('input[id^=channel-][id$=-split]:not(:disabled), input[id^=item-][id$=-split]:not(:disabled)').each(function() {
          sharetotal+=parseInt($(this).val());
        });

        // Normalize all shares to a percentage (& "store" in the boost section)
        $('input[id^=channel-][id$=-split]:not(:disabled), input[id^=item-][id$=-split]:not(:disabled)').each(function() {
          str=$(this).attr('id');
          hash=str.substring(0, str.lastIndexOf('-'));
          $('span#'+hash+'-pct').html((parseInt($(this).val())/sharetotal));
        });

        // Check if VTS is within the current timesec
        timesec=parseInt($('div#timeSlide').slider('value'));
        $('input[id$=-remotepct], input[id^=local-][id$=-split]').each(function() {
          str=$(this).attr('id');
          hash=str.substring(0, str.lastIndexOf('-'));
          start=parseInt($('input#'+hash+'-start').val());
          duration=parseInt($('input#'+hash+'-duration').val());
          if(timesec<start || timesec>=(start+duration)){  // Disable anything outside the time
            $('table tr#'+hash).each(function() { $(this).find('input').prop('disabled', true); });
            $('table tr#'+hash).css('color', '#aaa');
            $('span#'+hash+'-note').html('Not Active: Disabled');
            $('span#'+hash+'-note').css('color', '');
          }
          else{  // Highlight Active VTS
            $('span#'+hash+'-note').html('Active!');
            $('span#'+hash+'-note').css('color', 'green');
          }
        });
        
        // Process Active VTS'
        $('input[id^=remote-][id$=-remotepct]:not(:disabled), input[id^=local-][id$=-split]:not(:disabled)').each(function() {
          vtscfg=parseFloat($(this).val())/100;  // remotePercentage or localSplit
          vtspct=0;  // total percentage to VTS
          str=$(this).attr('id');
          vtshash=str.substring(0, str.lastIndexOf('-'));
          //Deduct the remotePercentage from all the "non-fee" splits and allocate to the VTS recipient
          $('[id$=-fee]:not(:checked):not(:disabled)').each(function() {
            str=$(this).attr('id');
            hash=str.substring(0, str.lastIndexOf('-'));
            oldpct=parseFloat($('span#'+hash+'-pct').html());  // Current non-fee split percentage
            newpct=oldpct*(1-vtscfg);  // Current split gets reduced by vtscfg (90%)
            vtspct+=oldpct*vtscfg;  // VTS percentage increases by vtscfg. (90%) of the non-fee split
            $('span#'+hash+'-pct').html(newpct);  // Update non-fee split percentage
          });
          $('span#'+vtshash+'-pct').html(vtspct); //update VTS percentage
        });

        //Multiply boost amount by each percentage & round to whole sats
        totalsats=totalpct=0;
        $('span[id$=-pct]').each(function() {
          str=$(this).attr('id');
          hash=str.substring(0, str.lastIndexOf('-'));
          if($(this).html()!='' && str!='total-pct'){ 
            pct=parseFloat($(this).html());
            sats=Math.round(pct*parseInt($('input#boost').val()));
            $('span#'+hash+'-sats').html(sats); 
            totalsats+=sats;
            totalpct+=pct;
          }

          //If it's an active VTS, build the Inception url with the sat amount & remoteStart
          hash=str.substring(str.indexOf('-') + 1, str.lastIndexOf('-'));
          if($('span#remote-'+hash+'-note').html()=='Active!'){
            url='?vts&feed='+$('span#remote-'+hash+'-note').attr('feedguid');
            if($('span#remote-'+hash+'-note').attr('itemguid')!=''){ url+='&item='+$('span#remote-'+hash+'-note').attr('itemguid'); }
            url+='&sats='+$('span#remote-'+hash+'-sats').html();  
            url+='&sec='+$('input#'+hash+'-remotestart').val();
            $('span#remote-'+hash+'-note').html('Active! -&gt; <a href="'+url+'">Split Inception!</a>');
          }
        });

        //For a sanity check, display total sats/percents at the bottom of the page.
        totals='Total Sats: <span id="total-sats">'+totalsats+'</span> &nbsp; ';
        totals+='Total Percent: <span id="total-pct">'+totalpct+'</span>';
        $('h3#totals').html(totals);

        //And format percentages (after done with calculations)
        $('span[id$=-pct]').each(function() { 
          if($(this).html()!=''){ $(this).html((parseFloat($(this).html())*100).toFixed(2)+'%'); } 
        });

      }

    })(jQuery);
  </script>
</head>
<body>
<form>
  <h1>Split Calculator - <span id="podcastTitle"></span></h1>
  <div class="boostcfg">
    <label for="boost">Boost Amount :</label><input id="boost" type="number" min="0" />
  </div>

  <h3>Channel Level Splits<a href="https://github.com/Podcastindex-org/podcast-namespace/blob/main/docs/1.0.md#value-recipient" style="text-decoration: none; margin-left: 0.5em;" target="_blank"><i class="pi pi-podcastindex"></i></a></h3>
  <div id="ctms"></div>
  <div id="channel"></div>

  <div id="vtsitem">
    <h3>Item Level Splits for <select id="episode"></select><a href="https://github.com/Podcastindex-org/podcast-namespace/blob/main/value/value.md#value-recipient-element" style="text-decoration: none; margin-left: 0.5em;" target="_blank"><i class="pi pi-podcastindex"></i></a></h3>
    <div id="itms"></div>
    <div id="item"></div>
  </div>

  <div id="vtsvts">
    <div class="boostcfg" style="margin-top: 1.5em;">
      <label for="time">Time (sec) :</label><div id="timeSlide"><div id="custom-handle" class="ui-slider-handle"></div></div>
    </div>

    <h3>Value Time Splits (Remote/Local) <a href="https://github.com/Podcastindex-org/podcast-namespace/blob/main/docs/1.0.md#value-time-split" style="text-decoration: none;" target="_blank"><i class="pi pi-podcastindex"></i></a></h3>
    <div id="vts"></div>
  </div>

  <a href="https://github.com/Cameron-IPFSPodcasting/Split-Calculator" target="_blank" style="margin-top: 1em; float: right;"><i class="pi pi-file" title="Source Code (GitHub)"></i></a>
  <h3 id="totals"></h3>
</form>
</body></html>
