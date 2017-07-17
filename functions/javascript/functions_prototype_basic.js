
var _running_timer = new Object();
var _running_browse = new Object();
var _running_bid_agents = new Object();
var _running_bid_agent_single = 0;
var _running_bid_account = 0;
var _bid_running = 0;
var _bid_agent_running = 0;

var clock_id = 0;

function add_to_watchlist(auction_id) {
  new Ajax.Request('/ajax/add-to-watchlist.html?aid=' + auction_id);
  $('beobachten').innerHTML = 'Auction added to watch list';
}

function print_server_time(s_time) {
 if(clock_id) {
   clearTimeout(clock_id);
   clock_id  = 0;
   s_time = parseInt(s_time)+1;
  }

  var tDate = new Date();
//  tDate.setTime(s_time);
  tDate.setTime(parseInt(s_time)*1000);
  _minutes = tDate.getMinutes();
  _secs = tDate.getSeconds();
  _hours = tDate.getHours();
  if (_minutes < 10) {
    _minutes = "0" + _minutes;
  }
  if (_secs < 10) {
    _secs = "0" + _secs;
  }
  if (_hours < 10) {
    _hours = "0" + _hours;
  }

  _display_time = _hours + ":" + _minutes + ":" + _secs;

  $('server_time').innerHTML = _display_time;

  clock_id = setTimeout("print_server_time(" + s_time + ")", 1000);
}


function validade_bid_agent_input(price1, price2, num_bids) {
  if (!check_price_field(price1)) {
    price1 = 0;
  }
  if (!check_price_field(price2)) {
    return false;
  }

  if (!num_bids.match(/^[0-9]+$/)) {
    num_bids = 0;
  }

  return true;
}

function check_price_field(price) {
  if (price.match(/^[0-9]+[,.]?[0-9]*$/)) {
    return true;
  } else {
    return false;
  }
}

function hidedisplay(div_id) {
  el = $(div_id);
  if (el) {
    if (el.style.display == "none") {
      el.style.display = "";

    } else {
      el.style.display = "none";
    }
  }
}

var closezoom = "";

function hidedisplayzoom(div_id) {
  if (closezoom != "") {
    closezoom.style.display = "none";
  }
  closezoom = $(div_id);

  el = $(div_id);
  if (el) {
    if (el.style.display == "none") {
      el.style.display = "";

    } else {
      el.style.display = "none";
    }
  }
}

function hidedisplay_fade(div_id, only_show) {
  el = $(div_id);
  if (el) {
    if (el.style.display == "none") {
      if (only_show != 1) {
        el.style.display = "";
      }
//      new Effect.BlindDown(div_id, {duration:.3});
    } else {
//      new Effect.BlindUp(div_id);
      el.style.display = "none";
    }
  }
}

function hidedisplay_show(div_id) {
  el = $(div_id);
  if (el) {
    el.style.display = "";
  }
}

function hidedisplay_dis(div_id) {
  el = $(div_id);
  if (el) {
    el.style.display = "none";
  }
}

function hidedisplay_help(div_id) {
  for (var i = 0; i < 100; i++) {
    el = document.getElementById('help_index_' + i);
    if (el) {
      el.style.display = 'none';
    }
  }
  hidedisplay(div_id);
}

function hidedisplay_helpheaders(div_id) {
  for (var i = 0; i < 15; i++) {
    el = document.getElementById('help_header_' + i);
    if (el) {
      el.style.display = 'none';
    }
  }
  hidedisplay(div_id);
}

function change_browse_page(div_name, url) {
//  new Ajax.Updater(div_name, url, {encoding: "utf-8"});
  get_browse_page(div_name, url);
}

function get_browse_page(div_name, url) {
  if (_running_browse[div_name] != null) {
    _running_browse[div_name].stop();
  }

  _running_browse[div_name] =
    new Ajax.PeriodicalUpdater(div_name, url, {encoding: 'utf-8', frequency:6000, evalScripts:true});
}


function dotimer(auction_id, total, div_name) {

  diff=total;//+40;


  if (total <= 1) {
    /* TODO get push data */
    start_auction_just_counter(auction_id, div_name, 2);
  } else {

  /* alert(auction_id + ":-:" + hours + ":" + minutes + ":" + seconds);*/

    el = $(div_name);

    if (el) {
      el.innerHTML = calc_counter_from_time(diff);
    }

    total--;

    if (_running_timer[auction_id] != ''
        && _running_timer[auction_id] != null) {
      window.clearTimeout(_running_timer[auction_id]);
    }

    _running_timer[auction_id] =
      window.setTimeout("dotimer(" + auction_id + ", " + total + ", '" + div_name + "')", 1000)
  }
}


function calc_counter_from_time(diff) {

  if (diff > 0) {
    hours=Math.floor(diff / 3600)

    minutes=Math.floor((diff / 3600 - hours) * 60)

    seconds=Math.round((((diff / 3600 - hours) * 60) - minutes) * 60)
  } else {
    hours = 0;
    minutes = 0;
    seconds = 0;
  }

  if (seconds == 60) {
    seconds = 0;
  }

  if (minutes < 10) {
    if (minutes < 0) {
      minutes = 0;
    }
    minutes = '0' + minutes;
  }
  if (seconds < 10) {
    if (seconds < 0) {
      seconds = 0;
    }
    seconds = '0' + seconds;
  }
  if (hours < 10) {
    if (hours < 0) {
      hours = 0;
    }
    hours = '0' + hours;
  }

  return hours + ":" + minutes + ":" + seconds;
}


/**
 * html place_bid(int, string)
 *  - ajax request which places bid + prints error
 *
 * @param int auction_id - Auction ID
 * @param string error_div - id of error div
 */
function place_bid(auction_id, error_div, user_id, do_request, is_detail_page) {
  if (_bid_running == 1 && ((new Date()).getTime()-_last_bid_placed)<600) {
    return;
  }

  _last_bid_placed = (new Date()).getTime();

  _bid_running = 1;
  if (user_id == undefined || user_id == null || user_id == '') {
    user_id = 0;
  }

  if (do_request == undefined || do_request == null || do_request == '') {
    do_request = 0;
  }

  if (is_detail_page == undefined || is_detail_page == null || is_detail_page == '') {
    is_detail_page = 0;
  }


  // clean error div
  el = $(error_div);
  el2 = $("fehlerfeld_" + auction_id);
  if (el2) {
    el2.style.display = "none";
  }
  if (el) {
    el.innerHTML = "";
  }
/*
  var opt = {
    // Use POST
    //method: 'post',
    // Send this lovely data
    //postBody: escape('thisvar=true&thatvar=Howdy&theothervar=2112'),
    // Handle successful response
    //parameters:'aid=' + auction_id,
    onSuccess: function(t) {
      _bid_running = 0;
      if (t.responseText == "OK" || t.responseText == "") {
        force_single_bid_update(auction_id, user_id);
        update_bid_account();
        if (is_detail_page == 1) {
//          update_bid_agent(auction_id);
        }
        if (do_request == 1) {
          do_counter_request(auction_id, user_id);
        }
      } else {
        el = $(error_div);
        if (el) {
          el.innerHTML = t.responseText;
        }
        hidedisplay_fade("fehlerfeld_" + auction_id);
       // window.setTimeout("hidedisplay_fade('fehlerfeld_" + auction_id + "', 1)", 3000);
      }
      // TODO force update
    },
    // Handle 404
    on404: function(t) {
        alert('Error 404: location "' + t.statusText + '" was not found.');
    },
    // Handle other errors
    onFailure: function(t) {
        alert('Error ' + t.status + ' -- ' + t.statusText);
    }
  }
*/
  url = '/ajax/place_bid.html?aid=' + auction_id;
  refreshDetails(url, 'place_bid_' + auction_id);

  window.setTimeout("update_savings_details(" + auction_id + ",1)", 1000);

  if (is_detail_page == 1) {
    _ct_force_update = 1;
    //do_counter_request(auction_id, user_id);
  }

//  new Ajax.Request('/ajax/place_bid.html?aid=' + auction_id, opt);
}


function _eval_place_bid(auction_id, user_id, response) {
  error_div = 'bid_error_div_' + auction_id;
  _bid_running = 0;
  do_request = 1;

  if (response == "OK" || response == "") {
    // force_single_bid_update(auction_id, user_id);
    update_bid_account();
    get_my_placed_bids('my_placed_bids', auction_id);
    new Ajax.Request(
      '/ajax/short-lived-incentives.html',
      {
        onSuccess: function(transport) {
          short_lived_incentives_response = transport.responseText.split("#");
          if (short_lived_incentives_response[0] > 0) {
            do_visible(parseInt(short_lived_incentives_response[0]),short_lived_incentives_response[1]);
          }
        }
      }
    );
    if (do_request == 1) {
      if (_single_auction_verify != "" && _single_auction_verify_id == auction_id) {
        do_counter_request(auction_id, user_id, 'detail2', _single_auction_verify);
      }
    }
    check_for_auto_replenishment();
  } else {
    el = $(error_div);
    if (el) {
      el.innerHTML = response;
    } else {
      error_div = 'fehlermeldung';
      el = $(error_div);
      if (el) {
        el.innerHTML = response;
      }
    }
    hidedisplay_fade("fehlerfeld_" + auction_id);
    //window.setTimeout("hidedisplay_fade('fehlerfeld_" + auction_id + "', 1)", 3000);
  }
  update_stats_header();
}

function _eval_pb(response) {
      _bid_running = 0;
      do_request = 0;
      if (response == "OK" || response == "") {
        update_bid_account();

        if (do_request == 1) {
          do_counter_request(auction_id, user_id);
        }

        new Ajax.Request(
          '/ajax/short-lived-incentives.html',
          {
            onSuccess: function(transport) {
              short_lived_incentives_response = transport.responseText.split("#");
              if (short_lived_incentives_response[0] > 0) {
                do_visible(parseInt(short_lived_incentives_response[0]),short_lived_incentives_response[1]);
              }
            }
          }
        );

        check_for_auto_replenishment();
      } else {
        el = $(error_div);
        if (el) {
          el.innerHTML = response;
        }
        hidedisplay_fade("fehlerfeld_" + auction_id);
      //  window.setTimeout("hidedisplay_fade('fehlerfeld_" + auction_id + "', 1)", 3000);
      }
  update_stats_header();
}

mlastNow = new Object();
function refreshbid(mUpdateURL, script_id) {
  if (mlastNow[script_id] == null || mlastNow[script_id] == undefined) {
    mlastNow[script_id] = 0;
  }
  if (((new Date()).getTime()-mlastNow[script_id])<300) return;

  mlastNow[script_id] = (new Date()).getTime();
  var script = document.createElement('script');

  script.type = 'text/javascript';
  script.src = mUpdateURL + "&now="+(new Date()).getTime();
  script.id = script_id + '_refresh_js';
    // remove old script-node (if there is one..)
  el = document.getElementById(script.id);
  if (el) {
    document.getElementsByTagName('head')[0].removeChild( el );
  }
  // set new script node
  document.getElementsByTagName('head')[0].appendChild(script);
}



function update_bid_account() {
  if (_running_bid_account == 0) {
    _running_bid_account = 1;
    options = {
      onComplete: function() {
                    _running_bid_account = 0;
                  }
    }
    new Ajax.Updater('bid_account_total_bids', '/ajax/bid_account.html', options);
  }
}

var _running_auto_replenishment = 0;
function check_for_auto_replenishment() {
  if (4 != 1) {
    return;
  }

  // return false for all as we have disabled this
  return;

  if (_running_auto_replenishment == 0) {
    _running_auto_replenishment = 1;
    time_stamp = new Date();
    url = '/ajax/auto_replenishment.html?t=' + time_stamp;
    if ($('center_error_msg_container')) {
      $('center_error_msg_container').style.display = 'none';
    }
    if ($('center_success_msg_container')) {
      $('center_success_msg_container').style.display = 'none';
    }
    hidedisplay('center_loading_msg_container');
    new Ajax.Request(url, {
      method: 'get',
      onSuccess: function(transport) {
        _running_auto_replenishment = 0;
        hidedisplay('center_loading_msg_container');
        if (transport.responseText.match(/NOTICE/)) {
          // DO NOTHING
        } else if (transport.responseText.match(/ERROR/)) {
          $('center_error_msg').innerHTML = 'Die TurboLader-Automatik konnte aufgrund einer fehlgeschlagenen Zahlungstransaktion leider nicht ausgeführt. Aus Sicherheitsgründen wurde die TurboLader-Automatik deaktiviert. Bitte nehmen Sie die gewünschten Einstellungen erneut vor.<br />Zu den Einstellungen für die TurboLader-Automatik gelangen Sie  <a href="/member/payment-management.html">hier</a>.';
          hidedisplay('center_error_msg_container');
        } else {
          $('center_success_msg').innerHTML = transport.responseText;
          hidedisplay('center_success_msg_container');
          window.setTimeout("hidedisplay('center_success_msg_container')", 7000);
          update_bid_account();
        }
      }
    });
  }
}

function update_bid_agent(auction_id) {
  if (auction_id == null || auction_id == undefined) {
    return;
  }

  if (_running_bid_agent_single == 0) {
    _running_bid_agent_single = auction_id;
  }

  if (_running_bid_agents[auction_id] != ''
      && _running_bid_agents[auction_id] != null) {
    window.clearTimeout(_running_bid_agents[auction_id]);
  }

  if (_running_bid_agent_single != auction_id) {
    return;
  }

  url = '/ajax/bid_agent.html?aid=' + auction_id;
//  new Ajax.Updater('bid_agent_ov', url);
  refreshDetails(url, 'bid_agent_updater');

  window.setTimeout('update_bid_agent(' + auction_id + ')', 60000);
}


function _eval_placed_bid_agents(response) {
  div_name = 'bid_agent_ov';
  if ($(div_name)) {
    $(div_name).innerHTML = response;
  }
}

function _eval_bid_agent(response) {
  bid_agent_div = 'bid_agent_ov';
  if ($(bid_agent_div)) {
    $(bid_agent_div).innerHTML = response;
  }
}


function place_bid_agent_form(form) {
  submit = 1;
  ed = "fehlermeldung";

  if (_bid_agent_running == 1) {
    return;
  } else {
    _bid_agent_running = 1;
  }

  if (form.aid == '') {
    alert('no auction_id set');
    submit = 0;
  }
  if (form.error_div != '') {
    ed = form.error_div.value;
  } else {
    ed = 'fehlermeldung';
  }
  if (form.uid == '' || form.uid == undefined || form.uid == null) {
    user_id = 0;
  } else {
    user_id = form.uid.value;
  }

  if (!validade_bid_agent_input(form.pll.value, form.plh.value, form.nbp.value)) {
//    submit = 0;
  }
  if (submit == 1) {
    if (user_id == undefined || user_id == null) {
      user_id = 0;
    }

//    place_bid_agent(form.aid.value, form.pll.value, form.plh.value, 4, form.nbp.value, ed);
    place_bid_agent(form.aid.value, form.pll.value, form.plh.value, 2, form.nbp.value, ed, user_id);
    form.pll.value="";
    form.plh.value="";
    form.nbp.value="";
  }
}

var _ba_from = 0;
var _ba_to = 0;
var _ba_nbp = 0;

/**
 * html place_bid(int, string)
 *  - ajax request which places bid + prints error
 *
 * @param int auction_id - Auction ID
 * @param string error_div - id of error div
 */
function place_bid_agent(auction_id, from, to, type, nbp, error_div, user_id) {
  if (user_id == undefined || user_id == null) {
    user_id = 0;
  }

  _ba_from = from;
  _ba_to = to;
  _ba_nbp = nbp;

  // clean error div
  bb_status_box = 'bid_agent_status_box';
  bb_status_box_text = 'bid_agent_status_box_text';
  el = $(error_div);
  el2 = $("fehlerfeldbb_" + auction_id);
  if (el2) {
    el2.style.display = "none";
  }
  if (el) {
    el.innerHTML = "";
  }
/*
  var opt = {
    onSuccess: function(t) {
      _bid_agent_running = 0;

      if (t.responseText == "OK" || t.responseText == "") {
        update_bid_account();
        update_bid_agent(auction_id);

        do_counter_request(auction_id, user_id);

        if (document.getElementById(bb_status_box_text)) {
          if (from == undefined || from == null || from == '') {
            if (_ct_counter_price == '' || _ct_counter_price == undefined || _ct_counter_price == null) {
              from_price = '0 &euro;';
            } else {
              from_price = _ct_counter_price;
            }
          } else {
            from_price = from + ' &euro;';
          }
          $(bb_status_box_text).innerHTML = from_price + ' bis ' + to + ' &euro; / ' + nbp + ' Gebote';
        }
        hidedisplay_fade(bb_status_box);
        window.setTimeout("hidedisplay_fade('" + bb_status_box + "', 1)", 4000);

      } else {

        el = $(error_div);
        if (el) {
          el.innerHTML = t.responseText;
        }
        hidedisplay_fade("fehlerfeldbb_" + auction_id);
       // window.setTimeout("hidedisplay_fade('fehlerfeldbb_" + auction_id + "', 1)", 3000);
      }
    },
    // Handle 404
    on404: function(t) {
        alert('Error 404: location "' + t.statusText + '" was not found.');
    },
    // Handle other errors
    onFailure: function(t) {
        alert('Error ' + t.status + ' -- ' + t.statusText);
    }
  }
*/
  url = '/ajax/place_bid_agent.html?nbp=' + nbp + '&abt=' + type + '&pll=' + from + '&plh=' + to + '&aid=' + auction_id;

  // add counter time to place_bid_url
  if (_ct_counter_status != undefined && _ct_counter_status != null && _ct_counter_status != "") {
    if (_ct_counter_status == _auction_status_live || _ct_counter_status == _auction_status_future) {
      if (_ct_counter_time != undefined) {
        url += '&cttime=' + _ct_counter_time;
      }
    }
  }

//  new Ajax.Request(url, opt);
  refreshDetails(url, 'place_bid_agent');
}

function delete_bid_agent(agent_id, auction_id) {
  new Ajax.Request(
     '/ajax/delete_bid_agent.html?aid=' + auction_id + '&baid=' + agent_id,
      {
        onComplete: function(transport) {
          update_bid_account();
          update_bid_agent(auction_id);
        }
      }
    );
}

function _eval_bid_agent_bid(auction_id, user_id, response) {
  _bid_agent_running = 0;
  bb_status_box = 'bid_agent_status_box';
  bb_status_box_text = 'bid_agent_status_box_text';
  error_div = 'fehlermeldungbb';

  if (_ba_from == undefined || _ba_from == null || _ba_from == '') {
    _ba_from = 0;
  }

  _ba_from = new String(_ba_from);
  _ba_to = new String(_ba_to);
  _ba_to = parseFloat(_ba_to.replace(/,/, "."));
  _ba_from = parseFloat(_ba_from.replace(/,/, "."));

  from = Math.round(parseFloat(_ba_from)*cent_conv);
  to = Math.round(parseFloat(_ba_to)*cent_conv);

  nbp = _ba_nbp;
  el = $(error_div);
  el2 = $("fehlerfeldbb_" + auction_id);
  if (el2) {
    el2.style.display = "none";
  }
  if (el) {
    el.innerHTML = "";
  }

  if (response == "OK" || response == "") {
    update_bid_account();
    update_bid_agent(auction_id);


    do_counter_request(auction_id, user_id);

    /* BB */
    new Ajax.Request(
      '/ajax/short-lived-incentives.html',
      {
        onSuccess: function(transport) {
          short_lived_incentives_response = transport.responseText.split("#");
          if (short_lived_incentives_response[0] > 0) {
            do_visible(parseInt(short_lived_incentives_response[0]),short_lived_incentives_response[1]);
          }
        }
      }
    );

    if (document.getElementById(bb_status_box_text)) {
      if (from == undefined || from == null || from == '') {
        if (_ct_counter_price == '' || _ct_counter_price == undefined || _ct_counter_price == null) {
          from_price = format_raw_to_price(0); //'0 &euro;';
        } else {
          from_price = format_raw_to_price(_ct_counter_price);
        }
      } else {
        from_price = format_raw_to_price(from);// + ' &euro;';
      }
      to_price = format_raw_to_price(to);
      $(bb_status_box_text).innerHTML = from_price + ' to ' + to_price + ' - ' + nbp + ' Bids';
    }
    hidedisplay_fade(bb_status_box);
    window.setTimeout("hidedisplay_fade('" + bb_status_box + "', 1)", 4000);
    check_for_auto_replenishment();
  } else {
    el = $(error_div);
    if (el) {
      el.innerHTML = response;
    }
    hidedisplay_fade("fehlerfeldbb_" + auction_id);
 //   window.setTimeout("hidedisplay_fade('fehlerfeldbb_" + auction_id + "', 1)", 3000);
  }
  update_stats_header();
}


function format_raw_to_price(price) {
  delimiter = ".";
  currency = "$";
  price = new String(price);
  price_length = price.length;
  if (price_length == 1) {
    return currency + "0.0" + price;
  } else if (price_length == 2) {
    return currency + "0." + price;
  } else {
    cent = price.substr((price_length-2), 2);
    return currency + price.substr(0, (price_length-2)) + delimiter + cent;
  }
}




function update_stats_header() {
  url = '/ajax/bubbles.html';
  new Ajax.Updater('auction_bubbles', url);
}


// function to update counter for short lived insentives
function display_short_lived_counter(seconds, div_name) {
  orig_seconds = seconds;
  if (seconds <= 0) {
  el = document.getElementById(div_name);

  if (el) {
    el.innerHTML = '00:00:00';
  }
    return;
  }
  hours = 0;
  minutes = 0;
  interval = 1000;
  if (seconds >= 3600) {
    hours = Math.floor(seconds/3600);
    seconds = Math.floor(seconds-(3600*hours));
  }
  if (seconds >= 60) {
    minutes = Math.floor(seconds/60);
    seconds = Math.floor(seconds-(60*minutes));
  }

  el = document.getElementById(div_name);

  if (el) {
    if (hours < 10) {
      hours = '0' + hours;
    }
    if (minutes < 10) {
      minutes = '0' + minutes;
    }
    if (seconds < 10) {
      seconds = '0' + seconds;
    }
    el.innerHTML = hours + ':' + minutes + ':' + seconds;
    orig_seconds -= 1;
    window.setTimeout("display_short_lived_counter(" + orig_seconds + ", \"" + div_name + "\")", interval);
  }
}




function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}

function resetbidderFromBidders(bidder) {

  var _tmpArray = [];
  _tmpArray.push(bidder);

  for (var i=0; i<bidders.length; i++) {
    if( bidders[i].is_bidder == 1 && bidder.is_bidder == 1){
      continue;
    }

    if (bidders[i].bidder_name != bidder.bidder_name) {
      _tmpArray.push(bidders[i]);
    }
  }
  return _tmpArray;

}

function removebidderFromBidders(bidder) {

  var _tmpArray = [];

  for (var i=0; i<bidders.length; i++) {
    if( bidders[i].is_bidder == 1 && bidder.is_bidder == 1){
      continue;
    }

    if (bidders[i].bidder_name != bidder.bidder_name) {
      _tmpArray.push(bidders[i]);
    }
  }
  return _tmpArray;
}

function addBidder(bidder){

  if(bidder == undefined || bidder.bidder_name == "" || bidder.bidder_name.length == 0){
    return false;
  }

  bidders = removebidderFromBidders(bidder);

  bidders[bidders.length]=bidder;

}

function setTimeDiff(_s_time){
  var _upTime = (new Date()).getTime();

  if(_s_time != undefined && _s_time != null) {
    if(_s_time > 0){
      if(_upTime >=  _s_time){
        _ms_diff = (_upTime-_s_time)*-1;
      } else {
        _ms_diff = _s_time-_upTime;
      }
    }
  }
}

function refreshBiddersList(){

  var _upTime = (new Date()).getTime() + _ms_diff;
  var i = 0;
  var _bidders_out = "";
  nb_bidders = 0;

  for(i=bidders.length-1;i>=0;i--){
    if(bidders[i].bid_time + bidders_ms_duration < _upTime) {
     continue;
    }
    if(i<bidders.length-1){
      _bidders_out += ", ";
    }
    if(bidders[i].is_bidder == 1){
      _bidders_out += '<b>You</b>';
    } else {
      _bidders_out +=bidders[i].bidder_name;
    }
    nb_bidders++ ;
  }

  if($('bidders')){
    $('bidders').innerHTML = _bidders_out;
  }
  if($('num_bidders_h')){
    $('num_bidders_h').innerHTML = nb_bidders;
  }
  if($('num_bidders_s')){
    $('num_bidders_s').innerHTML = nb_bidders;
  }
}

function removeAuctionFromList(aid, list) {

  var _tmpArray = [];

  for (var i=0; i<list.length; i++) {
  
    if( list[i] == aid){
      continue;
    }

    _tmpArray.push(list[i]);
  }
  return _tmpArray;
}

