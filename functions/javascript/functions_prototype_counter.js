/* <![CDATA[ */
//var http_request = null;
//var running = 0;

var _ct_counter_time = 0;
var _ct_counter_time_plain = 0;
var _ct_counter_winner_id = 0;
var _ct_counter_winner_name = '';
var _ct_counter_price = 0;
var _ct_counter_status = 0;
var _ct_counter_status_changed = 0;
var _ct_counter_last_life_update = 0;
var _ct_counter_last_update_skipped = 0;
var _ct_force_update = 0;
var _ct_max_retries = 5;
var _ct_max_wait_time = 30000;
var _ct_failed_requests_inc = 0;
var _ct_counter_latency_time = 1000;
var _ct_counter_last_price = 0;
var _ct_counter_force_update_time = 20;
var _ct_counter_force_update = 0;
var cent_conv = 100;

var _ct_first_ct_slot = 120;
var _ct_second_ct_slot = 20;

var _update_interval = 1000;
var _start_time = 0
var _cached_user_id = 0;

var _intv = '';
var _intv_index = '';
var _intv_special = '';
var _lock_ct_keys = Object();
var js_cd_speed_tics = new Object();
var _running_brw = new Object();
var _bid_history = new Array();
var _own_bid_history = new Array();
var _index_counter_prices = new Object();
var _bid_out_types = new Array();
var _wording_single_bid = 'Single bid';
var _wording_bid_agent = 'BidButler';
var _wording_bid_phone = 'Telephone';
var _last_update_ct_time = 0;
var _cds_image_deleted = 0;
_bid_out_types[1] = _wording_single_bid;
_bid_out_types[2] = _wording_bid_agent;
_bid_out_types[3] = _wording_bid_agent;
_bid_out_types[4] = _wording_bid_agent;
_bid_out_types[5] = _wording_bid_agent;
_bid_out_types[6] = _wording_bid_phone;

var _counter_reset = 40;
var _default_bid_increment = 10;

var _auction_status_future = 30;
var _auction_status_future_wo_startdate = 31;
var _auction_status_finished = 20;
var _auction_status_paused = 10;
var _auction_status_live = 1;
var _overwrite_timeout = 1000;
var _overwrite_timeout_index = 1000;
var _overwrite_timeout_special = 1000;

var _counter_server_prefix = '/telebid-us';

var _single_auction_verify = '';
var _single_auction_verify_id = 0;
var _multi_auction_verify = '';
var _multi_auction_verify_id = '';
var _special_auction_verify = '';
var _special_auction_verify_id = '';
var _last_bid_placed = 0;

var _ms_diff = 0;

var _requested_aids = new Array();
var _requested_spec_aids = new Array();
var _request_counter_aids = true;
var _request_counter_spec_aids = true;

/*
var _future_counter_running = 0;
var _future_counter_time = 0;
var _future_counter_changed = 0;
*/

var _last_bid_key = -1;

var _bid_history_blocked = 0;

var _counter_changed = 0

var _detail_request_running = 0;

var _last_action_div_displayed = 0;
var _auction_detail_last_update = 0;
var _index_auctions_js_last_update = 0;
var _special_auctions_js_last_update = 0;

var _is_refreshed = 0;
var bidders = new Array();
var bidders_ms_duration = 900000;
var nb_bidders = 0;

var _first_load = 1;

function _set_refreshed_auction(){
  _is_refreshed = 1;
}

/**
 * void auction_detail_js(int, int, int)
 *   - init counter requests on detail pages
 */
function auction_detail_js(auction_id, auction_status, start_time, user_id, verify) {
  if (user_id == undefined || user_id == null) {
    user_id = 0;
  } else {
    if (user_id != 0) {
      _cached_user_id = user_id;
    }
  }

  if (verify == undefined || verify == null) {
    verify = '';
  }

  if (_cached_user_id != 0) {
    user_id = _cached_user_id;
  }

  if (auction_status == _auction_status_finished) {
    if (_intv != '' || _intv != null) {
      window.clearInterval(_intv);
    }
    return;
  }

  if (start_time > 0) {
    _start_time = start_time;
  }

  if (_lock_ct_keys['detail'] == undefined || _lock_ct_keys['detail'] == null) {
    _lock_ct_keys['detail'] = 0;
  }

  _do_real_counter_request = 1;
  __ct_update_period = 4000;
  if (_ct_counter_time_plain != "") {
    if (__ct_update_period > _ct_first_ct_slot) {
      __ct_update_period = 6000;
    } else if (__ct_update_period > _ct_second_ct_slot) {
      __ct_update_period = 4000;
    } else {
      __ct_update_period = 3000;
    }

    if (parseInt(_ct_counter_time_plain) <= _ct_counter_force_update_time) {
      _do_real_counter_request = 1;
      _ct_counter_force_update = 1;
    } else {
      _ct_counter_force_update = 0;
      if (parseInt(_ct_counter_time_plain) > _ct_first_ct_slot
          && ((new Date()).getTime()-_auction_detail_last_update) < 5500) {
        _do_real_counter_request = 0;
      } else if (parseInt(_ct_counter_time_plain) > _ct_second_ct_slot
        && ((new Date()).getTime()-_auction_detail_last_update)<2500) {
        _do_real_counter_request = 0;
      }

      if (_ct_counter_time_plain > 1 && _ct_failed_requests_inc < _ct_max_retries) {
        if (_ct_counter_last_life_update == 0
            || ((new Date()).getTime()-_ct_counter_last_life_update) > 600) {
          _ct_counter_time_plain--;
          _ct_counter_time = calc_counter_from_time(_ct_counter_time_plain);
          _update_interval = 950;
          _overwrite_timeout = "";
          $('countertime').innerHTML = _ct_counter_time;// + "'";
        } else {
          $('countertime').innerHTML = $('countertime').innerHTML;// + ".";
        }
      } else if (_ct_counter_time_plain == 1 || _ct_failed_requests_inc >= _ct_max_retries) {
        __ct_counter_connect_image = '<img src="http://img2.swoopo.com/telebid-us/img/connecting_counter.gif" width="170" height="45" />';
        if (_ct_counter_last_update_skipped == 1) {
          if ($('countertime')) {
            if ($('countertime').innerHTML != __ct_counter_connect_image) {
              $('countertime').innerHTML = __ct_counter_connect_image;
            } else {
//              $('countertime').innerHTML = "foo";
            }
          }
        } else {
          _ct_counter_last_update_skipped = 1;
        }
      }
    }
  }

  // force update if bid was placed
  if (_ct_force_update == 1) {
    _do_real_counter_request = 1;
    _ct_force_update = 0;
  }

  // 1. get counter data
  if (_lock_ct_keys['detail'] == 0) {
    if (_do_real_counter_request == 1) {
      _auction_detail_last_update = (new Date()).getTime();
      _lock_ct_keys['detail'] = 1;
      do_counter_request(auction_id, user_id, 'detail', verify);
    }
  } else {
    if (((new Date()).getTime()-_auction_detail_last_update)>__ct_update_period) {
       _lock_ct_keys['detail'] = 0;
    }
  }

  // 2. process auction status
  get_interval_by_status(auction_id);
  // 3. check_status_code
  check_status_code();

  // update start_time
  _start_time -= (_update_interval/1000);

  _single_auction_verify = verify;
  _single_auction_verify_id = auction_id;


  if (_overwrite_timeout != "") {
    _update_interval -= parseInt(_overwrite_timeout);
    if (_update_interval <= 250) {
      _update_interval = 1000;
    } else {
      _update_interval += 30;
    }
  }

  if (_intv != '' || _intv != null) {
    window.clearInterval(_intv);
  }
  _intv = setInterval('auction_detail_js(' + auction_id + ', ' + _ct_counter_status + ', ' + _start_time + ', ' + user_id + ', "' + verify + '")', _update_interval);
}

/**
 * void auction_index_js(int, int, int)
 *   - init counter requests on index pages
 */
function auction_index_js(auction_ids, user_id, verify) {
  if (auction_ids == '' || auction_ids == null) {
    return;
  }
  
  if(_requested_aids == '' || _requested_aids == null){
    _requested_aids = auction_ids.split(',');
  }
  
  if(_request_counter_aids == false){
   window.clearTimeout(_intv_index);
   return;    
  }

  if (user_id == undefined || user_id == null) {
    user_id = 0;
  }

  if (verify == undefined || verify == null) {
    verify = '';
  }

  
  if (_lock_ct_keys['index'] == undefined || _lock_ct_keys['index'] == null) {
    _lock_ct_keys['index'] = 0;
  }

  if (_lock_ct_keys['index'] == 0) {
    _multi_auction_verify = verify;
    _multi_auction_verify_id = auction_ids;
    do_counter_request_index(auction_ids, user_id, 'index', verify);
    _index_auctions_js_last_update =  (new Date()).getTime();
  } else {
    if (((new Date()).getTime()-_index_auctions_js_last_update)>3000) {
       _lock_ct_keys['index'] = 0;
    }
  }



  update_interval_index = 1000;
  if (parseInt(_overwrite_timeout_index) > 500) {
    update_interval_index = _overwrite_timeout_index;
  }
  if (_intv_index != '') {
    window.clearTimeout(_intv_index);
  }

  _intv_index = setTimeout('auction_index_js("' + auction_ids + '", "' + user_id + '", "' + verify + '")', update_interval_index);
}


/**
 * void auction_index_js(int, int, int)
 *   - init counter requests on index pages
 */
function auction_special_js(special_auction_ids, user_id, verify) {
  if (special_auction_ids == '' || special_auction_ids == null) {
    return;
  }
  
  if(_requested_spec_aids == '' || _requested_spec_aids == null){
    _requested_spec_aids = special_auction_ids.split(',');
  }

  if(_request_counter_spec_aids == false){
   window.clearTimeout(_intv_special);
   return;    
  }

  if (user_id == undefined || user_id == null) {
    user_id = 0;
  }

  if (verify == undefined || verify == null) {
    verify = '';
  }


  if (_lock_ct_keys['special'] == undefined || _lock_ct_keys['special'] == null) {
    _lock_ct_keys['index2'] = 0;
  }

  if (_lock_ct_keys['special'] == 0) {
    _special_auction_verify = verify;
    _special_auction_verify_id = special_auction_ids;
    do_counter_request_index(special_auction_ids, user_id, 'special', verify);
    _special_auctions_js_last_update =  (new Date()).getTime();
  } else {
    if (((new Date()).getTime()-_special_auctions_js_last_update)>3000) {
       _lock_ct_keys['special'] = 0;
    }
  }


  update_interval_special_auctions = 1000;
  if (parseInt(_overwrite_timeout_special) > 500) {
    update_interval_special_auctions = _overwrite_timeout_special;
  }
  if (_intv_special != '') {
    window.clearTimeout(_intv_special);
  }

//alert(" test " + update_interval_index2);
  _intv_special = setTimeout('auction_special_js("' + special_auction_ids + '", "' + user_id + '", "' + verify + '")', update_interval_special_auctions);
}


function parse_counter_response_index(transport, plain) {
  if (plain != undefined && plain != null && plain == 1) {
    counters = transport.split('#');
  } else {
    counters = transport.responseText.split('#');
  }
  for (i = 0; i <= counters.length; i++) {
    if (counters[i] == null) continue;
    counter_data = counters[i].split(':');
    auction_id = counter_data[0];
    div_name_counter = 'counter_index_page_' + auction_id;
    div_price_name = 'price_index_page_' + auction_id;
    div_name_winner = 'winner_index_page_' + auction_id;
    div_button_name = 'button_index_page_' + auction_id;
    div_button_name_finished = 'button_finished_index_page_' + auction_id;

    div_name_counter_spec =  'spec_countertime_' + auction_id;
    div_price_name_spec =  'spec_current_price_' + auction_id;
    div_name_winner_spec =  'spec_current_winner_' + auction_id;

    counter = counter_data[1].split('|');
    for (ii = 0; ii < counter.length; ii++) {
      data = counter[ii].split('=');
      if (data[0] == 'ct') {
        if (data[1] == '-') {
          _ct_time = '--:--:--';
        } else {
          _ct_time = calc_counter_from_time(data[1]);
        }
        if ($(div_name_counter)) {
          $(div_name_counter).innerHTML = _ct_time;
          if (parseInt(data[1]) <= 10 && parseInt(data[1]) > 0) {
            $(div_name_counter).style.color = '#DD0000';
          } else {
            $(div_name_counter).style.color = '';
          }
        }
         if ($(div_name_counter_spec)) {
          $(div_name_counter_spec).innerHTML = _ct_time;
          if (parseInt(data[1]) <= 10 && parseInt(data[1]) > 0) {
            $(div_name_counter_spec).style.color = '#DD0000';
          } else {
            $(div_name_counter_spec).style.color = '';
          }
        }
      } else if (data[0] == 'cs') {
        if (data[1] == _auction_status_paused) {
          _cur_time = new Date();
          _cur_secs = (_cur_time.getTime()/1000);
          if (_cur_secs%4 <= 2 && $(div_name_counter)) {
            $(div_name_counter).innerHTML = 'Pause';
          }
          if (_cur_secs%4 <= 2 && $(div_name_counter_spec)) {
            $(div_name_counter_spec).innerHTML = 'Pause';
          }
        } else if (data[1] == _auction_status_finished) {

          _requested_aids = removeAuctionFromList(auction_id, _requested_aids);
          _requested_spec_aids = removeAuctionFromList(auction_id, _requested_spec_aids);
          
          if(_requested_aids.length == 0) {
            _request_counter_aids = false;
          }
          
          if(_requested_spec_aids.length == 0) {
            _request_counter_spec_aids = false;
          }

          hidedisplay_show(div_button_name_finished);
          hidedisplay_dis(div_button_name);
          if ($(div_name_counter)) {
            $(div_name_counter).innerHTML = 'Ended';
          }
          if ($(div_name_counter_spec)) {
            $(div_name_counter_spec).innerHTML = 'Ended';
          }
        }
      } else if (data[0] == 'cw' && data[1] != '') {
        el = $(div_name_winner);
        if (el) {
          el.innerHTML = data[1];
        }
        if($(div_name_winner_spec)){
          $(div_name_winner_spec).innerHTML = data[1];
        }
      } else if (data[0] == 'cp') {
        if ($(div_price_name)) {
          $(div_price_name).innerHTML = format_raw_to_price(data[1]);
        }
        if ($(div_price_name_spec)) {
          $(div_price_name_spec).innerHTML = format_raw_to_price(data[1]);
        }
        if (_index_counter_prices[auction_id] == null
            || _index_counter_prices[auction_id] == '') {
          _index_counter_prices[auction_id] = data[1];
        } else {
          if (_index_counter_prices[auction_id] != data[1]) {
            new Effect.Highlight(div_price_name, {duration:1,startcolor:'#ff0000', endcolor:'#FFFFFF', restorecolor:'#FFFFFF'});
            _index_counter_prices[auction_id] = data[1];
          }
        }
      }
    }
  }
}

var mlastNow = Object();


function refreshDetails(mUpdateURL, script_id) {
  if (mlastNow[script_id] == null || mlastNow[script_id] == undefined) {
    mlastNow[script_id] = 0;
  }

  if (((new Date()).getTime()-mlastNow[script_id])<300) return;

  mlastNow[script_id] = (new Date()).getTime();
  var script = document.createElement('script');

  script.type = 'text/javascript';
  script.src = mUpdateURL + "&now="+(new Date()).getTime();
  script.id = script_id + '_refresh_js' + '4';

  // remove old script-node (if there is one..)
  el = document.getElementById(script.id);
  if (el) {
    document.getElementsByTagName('head')[0].removeChild( el );
  }

  // set new script node
  document.getElementsByTagName('head')[0].appendChild(script);
}


var single_updates = new Object();
function force_single_bid_update(auction_id, user_id) {
  if (single_updates[auction_id] == undefined || single_updates[auction_id] == null) {
    single_updates[auction_id] = 0;
  }

  if (single_updates[auction_id] == 1) {
    return;
  }

  script_id = auction_id + '_single';

  url = _counter_server_prefix + '/counter/' + auction_id + '.html?plain=1&ext=1';
  if (user_id != undefined && user_id != null && user_id != 0) {
    url += '&uid=' + user_id;
  }

  refreshDetails(url, script_id);
}



/**
 * void do_counter_request(int)
 *   - do counter request and call success function
 */
function do_counter_request(auction_id, user_id, lock_key, verify) {
  url = _counter_server_prefix + '/counter/' + auction_id + '.html?plain=1';
  if (user_id != undefined && user_id != null && user_id != 0) {
    url += '&uid=' + user_id;
  }

  if (verify != undefined && verify != null) {
    url += '&val=' + verify;
  }

  if (lock_key == undefined || lock_key == null || lock_key == 0) {
    lock_key = 0;
  }

  if (_last_bid_key > -1) {
    url += '&lbp=' + _last_bid_key;
  }
//  refreshDetails(url, lock_key);
//alert(url);

  options = {method: 'get',
    onSuccess: function(transport) {
//                  parse_counter_response(transport, auction_id);
                  eval(transport.responseText);
                }
  }
  if (lock_key != 0) {
    if (_lock_ct_keys[lock_key] == undefined || _lock_ct_keys[lock_key] == null || _lock_ct_keys[lock_key] == 0) {
      _lock_ct_keys[lock_key] = 1;
    }
    options = {method: 'get',
      onSuccess: function(transport) {
//                    parse_counter_response(transport, auction_id);
                    eval(transport.responseText);
                  },
      onComplete: function(transport) {
                    _lock_ct_keys[lock_key] = 0;
                  }
    }
  }

  new Ajax.Request(url, options);

}


function _ev_detail(transport, auction_id, restet_counter_time, request_user_time) {
  if (restet_counter_time != null && restet_counter_time != undefined
      && restet_counter_time != '' && restet_counter_time != 0) {
    //_overwrite_timeout = restet_counter_time;
    _overwrite_timeout = (1000-restet_counter_time);
  }

  if (typeof(request_user_time) != "undefined"
      && null != request_user_time
      && parseInt(request_user_time) > 0) {
    //$('a_current_winner').innerHTML = request_user_time + " - " + ((new Date()).getTime()-parseInt(request_user_time));
    if (((new Date()).getTime()-parseInt(request_user_time)) > _ct_counter_latency_time) {
      //$('a_current_winner').innerHTML = request_user_time + " - " + ((new Date()).getTime()-parseInt(request_user_time)) + " - skipped";
      if (_ct_counter_force_update == 0) {
        _lock_ct_keys['detail'] = 0;
        _ct_counter_last_update_skipped = 1;
        _ct_failed_requests_inc++;
        return "";
      }
    }
  }

  _ct_counter_last_update_skipped = 0;
  _ct_failed_requests_inc = 0;

  parse_counter_response(transport, auction_id, 1);

//  if ($('error_debug3')) {
//    $('error_debug3').innerHTML = "rct: " + restet_counter_time;
//  }
  _lock_ct_keys['detail'] = 0;
}

function _ev_index(transport, auction_id, restet_counter_time) {
  if (restet_counter_time != null && restet_counter_time != undefined
      && restet_counter_time != '' && restet_counter_time != 0) {
    _overwrite_timeout_index = (1000-restet_counter_time);
    _overwrite_timeout_special = (1000-restet_counter_time);
    //_overwrite_timeout_index = (restet_counter_time-1000);
  }
  parse_counter_response_index(transport, 1);
  _lock_ct_keys['index'] = 0;
  if (_lock_ct_keys['special'] != null) {
    _lock_ct_keys['special'] = 0;
  }
}

function _ev_ext(transport, auction_id, restet_counter_time) {
  if (restet_counter_time != null && restet_counter_time != undefined
      && restet_counter_time != '' && restet_counter_time != 0) {
    _overwrite_timeout_index = restet_counter_time;
    _overwrite_timeout_special = restet_counter_time;
  }

  parse_counter_response_ext(transport, auction_id);
}


function do_counter_request_index(auction_ids, user_id, lock_key, verify) {
  if (auction_ids == '' || auction_ids == null) {
    return;
  }

  url = _counter_server_prefix + '/counter/1.html?aids=' + auction_ids;
  if (user_id != undefined && user_id != null && user_id != 0) {
    url += '&uid=' + user_id;
  }
  if (verify != undefined && verify != null) {
    url += '&val=' + verify;
  }
  if (lock_key == undefined || lock_key == null || lock_key == 0) {
    lock_key = 0;
  }
  if (lock_key != 0) {
    if (_lock_ct_keys[lock_key] == undefined || _lock_ct_keys[lock_key] == null || _lock_ct_keys[lock_key] == 0) {
      _lock_ct_keys[lock_key] = 1;

      //refreshDetails(url, 'index');
//      refreshDetails(url, lock_key);

  options = {method: 'get',
    onSuccess: function(transport) {
                  //parse_counter_response_index(transport);
                  eval(transport.responseText)
                }
  }

  if (lock_key != 0) {
    if (_lock_ct_keys[lock_key] == undefined || _lock_ct_keys[lock_key] == null || _lock_ct_keys[lock_key] == 0) {
      _lock_ct_keys[lock_key] = 1;
    }
    options = {method: 'get',
      onSuccess: function(transport) {
                    //parse_counter_response_index(transport);
                    eval(transport.responseText)
                  },
      onComplete: function(transport) {
                    _lock_ct_keys[lock_key] = 0;
                  }
    }
  }

  new Ajax.Request(url, options);


    }
  }

}


/**
 * void check_status_code(void)
 *   - get status code specific HTML
 */
function check_status_code() {
  if (_ct_counter_status == _auction_status_finished) {
    if ($('gebotsbutton')) {
      $('gebotsbutton').innerHTML = '<strong>Auction ended!</strong>';
    }
    if ($('bietbutler')) {
      $('bietbutler').innerHTML = '';
    }
    window.location.reload();
  }
}



/**
 * void get_future_auction_counter(int, int)
 *   - display future auction counter
 */
/*
function get_future_auction_counter(auction_id, start_time) {
  if (_future_counter_time > 0 && _future_counter_changed == 1) {
    start_time = _future_counter_time;
    _future_counter_changed = 0;
  }
  if (start_time < 0 || _ct_counter_status != _auction_status_future) {
    return;
  }

  time_string = calc_counter_from_time(start_time);

  $('countertime').innerHTML = time_string;

  _intv2 = setTimeout('get_future_auction_counter(' + auction_id + ', ' + --start_time + ')', 1000);
}
*/


/**
 * void get_interval_by_status(int)
 *   - check for counter status and adapt update interval
 */
function get_interval_by_status(auction_id) {

  // set interval
  if (_ct_counter_status_changed == 1
      || _ct_counter_status == _auction_status_future) {
    if (_ct_counter_status != 0) {
      if (_ct_counter_status == _auction_status_future) {
        // future auction
        _update_interval = 1000;
        if (_start_time > 0) {
          _update_interval = (_start_time*1000);
          if (_start_time < 10) {
            _update_interval = 1000;
          } else if (_update_interval > 5000 || _update_interval < 0) {
            _update_interval = 1000;
          }
        }
      } else if (_ct_counter_status == _auction_status_paused) {
        _update_interval = 2000;
      } else if (_ct_counter_status == _auction_status_live) {
        _update_interval = 1000;
      }
    }


    if (_ct_counter_status_changed == 1) {
      //alert('status changed - ' + _update_interval + ' - TODO print it');
    }

    if (_ct_counter_status == _auction_status_live
        || _ct_counter_status == _auction_status_future) {

      if (parseInt(_overwrite_timeout) > 500) {
        _update_interval = _overwrite_timeout;
      }
    }

    _ct_counter_status_changed = 0;
    //alert('status changed - ' + _update_interval);
    //auction_detail_js(auction_id, _ct_counter_status, 0);
  }
}



function parse_counter_response_ext(transport, auction_id) {

  if (transport == undefined || transport == null) {
    return;
  }
  result_array = transport.split("|");

  div_counter_name = 'countertime';
  div_winner = 'a_current_winner';
  div_price_name = 'a_current_price';

  div_counter_name_index = 'counter_index_page_' + auction_id;
  div_price_index = 'price_index_page_' + auction_id;
  div_winner_index = 'winner_index_page_' + auction_id;


  for (i = 0; i < result_array.length; i++) {
    if ('' == result_array[i] || null == result_array[i]) {
      continue;
    }
    data = result_array[i].split("=");
    if (data.length < 2) {
      continue;
    }
    if (data[0] == 'ct' && data[1] != '') {
      _ct_counter_time = data[1];
      if (_ct_counter_time != undefined && _ct_counter_time != null && _ct_counter_time != '') {
        _ct_counter_time = calc_counter_from_time(_ct_counter_time);
      } else {
        _ct_counter_time = '';
      }
      if (_ct_counter_time != '' && _ct_counter_time != '-') {
        if ($(div_counter_name)) {
          $(div_counter_name).innerHTML = _ct_counter_time;
        } else if ($(div_counter_name_index)) {
          $(div_counter_name_index).innerHTML = _ct_counter_time;
        }
      }
    }  else if (data[0] == 'cw' && data[1] != '') {
      if ($(div_winner_index)) {
        $(div_winner_index).innerHTML = data[1];
      } else if ($(div_winner)) {
        $(div_winner).innerHTML = data[1];
      }
    }  else if (data[0] == 'cp' && data[1] != '') {
      if ($(div_price_index)) {
        $(div_price_index).innerHTML = data[1];
//        new Effect.Highlight(div_price_index, {duration:1,startcolor:'#ff0000',endcolor:'#ffffff',restorecolor:'#FFFFFF'});
      } else if ($(div_price_name)) {
        $(div_price_name).innerHTML = data[1];
//        new Effect.Highlight(div_price_name, {duration:1,startcolor:'#ff0000',endcolor:'#ffffff',restorecolor:'#FFFFFF'});
      }
    }
  }
  return;
}



/**
 * void parse_counter_response(object int)
 *   - parse request from counter server and adapt html
 */
function parse_counter_response(transport, auction_id, plain) {
  if (plain != undefined && plain != null && plain == 1) {
    result_array = transport.split("|");
  } else {
    result_array = transport.responseText.split("|");
  }

  div_counter_name = 'countertime';

  // FELIX NEW
  has_price_update = 0;
  tmp_counter = 0;
  for (i = 0; i < result_array.length; i++) {
    tmp_data = result_array[i].split("=");
    if (tmp_data.length < 2) {
      continue;
    }
    if (tmp_data[0] == 'ct') {
      tmp_counter = tmp_data[1];
    }
    if (tmp_data[0] == 'cp') {
      has_price_update = 1;
    }
    if (tmp_data[0] == 'ra' && tmp_data[1] == 1) {
      if(_is_refreshed == 0){
        // reload if ra
        window.location.reload();
      }
    }
  }

  if (has_price_update == 0 && _ct_counter_time_plain == tmp_counter
      && _ct_counter_force_update == 0) {
//    $('a_current_winner').innerHTML = 'skipped...';
    return "";
  }
//  $('a_current_winner').innerHTML = "fff : " + has_price_update + " : " + _ct_counter_time_plain + " : " + tmp_counter;
  // EOF FELIX NEW

  for (i = 0; i < result_array.length; i++) {
    if ('' == result_array[i] || null == result_array[i]) {
      continue;
    }
    data = result_array[i].split("=");
    if (data.length < 2) {
      continue;
    }

    if (_last_bid_key != null) {
      if (js_cd_speed_tics != undefined && js_cd_speed_tics != null) {
        _last_cs_speed_matched = 0;
        _current_speed = 20;
        for (var ii in js_cd_speed_tics) {
          if (_last_cs_speed_matched == 1) {
            _update_counter_speed_image(ii, js_cd_speed_tics[ii], _current_speed);
            _last_cs_speed_matched = 0;
          }
          if (parseInt(_last_bid_key) >= parseInt(js_cd_speed_tics[ii])) {
            _last_cs_speed_matched = 1;
            _current_speed = ii;
          }
        }
        if (_last_cs_speed_matched == 1) {
          _update_counter_speed_image(_current_speed, 1000, _current_speed);
          _delete_counter_speed_image(_current_speed);
          _last_cs_speed_matched = 0;
        }
      }
    }


    if (data[0] == 'ct') {
      _ct_counter_time = data[1];
      _ct_counter_time_plain = data[1];
      _ct_counter_last_life_update = (new Date()).getTime();

//      if (_ct_counter_status != _auction_status_future) {
      if (_ct_counter_status == _auction_status_future_wo_startdate) {
        _ct_counter_time = '-- : -- : -- ';
      } else {
        if (_ct_counter_time == '-') {
          _ct_counter_time = '-- : -- : --';
        } else if (_ct_counter_status == _auction_status_live
                   && (_ct_counter_time == '0' || _ct_counter_time == '')) {
          _ct_counter_time = '';
        } else {
          _ct_counter_time = calc_counter_from_time(_ct_counter_time);
        }
      }

      if ($(div_counter_name) && _ct_counter_time != '') {
        $(div_counter_name).innerHTML = _ct_counter_time;
        if (parseInt(data[1]) <= 10 && parseInt(data[1]) > 0) {
          $(div_counter_name).style.color = '#DD0000';
          //$('countertime').style.textDecoration = 'blink'
          if ($('last_actions_div').style.display == 'none'
              &&  $('last_actions_div').innerHTML != '<img src="http://img2.swoopo.com/telebid-us/img/blink.gif">') {
            $('last_actions_div').innerHTML = '<img src="http://img2.swoopo.com/telebid-us/img/blink.gif">';
            $('last_actions_div').style.display = '';
          }
        } else {
          if (_last_action_div_displayed == 0) {
            $('countertime').style.color = '';
            //$('countertime').style.textDecoration = 'none';
            //$('last_actions_div').innerHTML = '';
            $('last_actions_div').style.display = 'none';
          }
        }
      } else if ($('counter_index_page_' + auction_id)) {
        $('counter_index_page_' + auction_id).innerHTML = _ct_counter_time;
      }


//      } else {
//        _future_counter_time =
//          (parseInt(_ct_counter_time) + parseInt(_start_time));
//        _ct_counter_time = calc_counter_from_time(_future_counter_time);
//         $('countertime').innerHTML = _ct_counter_time;
//        _future_counter_changed = 1;
//      }
    } else if (data[0] == 'cw' && data[1] != '') {
      _ct_counter_winner_name = data[1];
      if ($('a_current_winner')) {
        $('a_current_winner').innerHTML = _ct_counter_winner_name;
      }
      if ($('winner_index_page_' + auction_id)) {
        $('winner_index_page_' + auction_id).innerHTML = _ct_counter_winner_name;
      }
    } else if (data[0] == 'cp') {
      _ct_counter_price = data[1];
      if ($('a_current_price')) {
        $('a_current_price').innerHTML = format_raw_to_price(_ct_counter_price);
      }
      if ($('a_current_price2')) {
        $('a_current_price2').innerHTML = format_raw_to_price(_ct_counter_price);
      }
      if ($('price_index_page_' + auction_id)) {
        $('price_index_page_' + auction_id).innerHTML = format_raw_to_price(_ct_counter_price);
      }

      if (_ct_counter_price != _counter_changed) {
        //new Effect.Highlight('a_current_winner', {duration:1,startcolor:'#ff0000'});
        if (_last_bid_key != null && _last_bid_key != undefined
            && _last_bid_key > 0) {
          if ($('a_current_price')) {
             $('a_current_price').style.backgroundColor='#FFFFFF';
             new Effect.Highlight('a_current_price', {duration:1,startcolor:'#ff0000',endcolor:'#ffffff',restorecolor:'#FFFFFF'});
          } else if ($('price_index_page_' + auction_id)) {
            new Effect.Highlight('price_index_page_' + auction_id, {duration:1,startcolor:'#ff0000',endcolor:'#ffffff',restorecolor:'#FFFFFF'});
          }
        } else if ($('price_index_page_' + auction_id)) {
          new Effect.Highlight('price_index_page_' + auction_id, {duration:1,startcolor:'#ff0000',endcolor:'#ffffff',restorecolor:'#FFFFFF'});
        }
      }

      if (_last_bid_key != -1) {
        // update savings
        update_savings_details(auction_id,0);
//        _calc_savings_detail_by_params();
      }
      // counter changed
      _counter_changed = _ct_counter_price;

    } else if (data[0] == 'cs') {
      if (data[1] != _ct_counter_status) {
        _ct_counter_status_changed = 1;
      }
      _ct_counter_status = data[1];

      // display puased if needed
      if (_ct_counter_status == _auction_status_paused) {
        _cur_time = new Date();
        _cur_secs = _cur_time.getTime();
        if (_cur_time%5 <= 2) {
          $(div_counter_name).innerHTML = 'Pause';
        }
      }
    } else if (data[0] == 'cwi') {
      _ct_counter_winner_id = data[1];
    } else if (data[0] == 'bh' && data[1] != '') {
      parse_bid_history(data[1], auction_id);

      if($('bid_agent_ov')){
         if($('bid_agent_ov').getElementsByTagName("td") != null
            && $('bid_agent_ov').getElementsByTagName("td") != undefined){
           if($('bid_agent_ov').getElementsByTagName("td").length > 1) {
            update_bid_agent(auction_id);
          }
         }
      }

    } else if (data[0] == 'lui') {
      parse_last_update_info(data[1]);
    }
  }

  refreshBiddersList();

  _detail_request_running = 0;
}


var _lui_running = '';
function parse_last_update_info(lui) {
  lui_data = lui.split('#');
  lui_out = '';
  lui_div = 'last_actions_div';
  if (null == bid_increment || undefined == bid_increment) {
    bid_increment = _default_bid_increment;
  }

  if (lui_data.length == 4) {
    if (lui_data[0] != null && lui_data[0] != undefined
        && lui_data[1] != null && lui_data[1] != undefined) {
      if (lui_data[0] != 0 && lui_data[1] != 0) {
        lui_out += lui_data[1] + ' ';
        lui_out += _wording_single_bid;
        if (lui_data[1] > 1) {
          lui_out += 's';
        }
        lui_out += ' + ' + format_raw_to_price(parseInt(bid_increment)*parseInt(lui_data[1]))
        lui_out += ' + ' + calc_counter_from_time(lui_data[0]);
        lui_out += '<br />';
      }
    }

    if (lui_data[2] != null && lui_data[2] != undefined
        && lui_data[3] != null && lui_data[3] != undefined) {
      if (lui_data[2] != 0 && lui_data[3] != 0) {
        lui_out += lui_data[3] + ' ';
        lui_out += _wording_bid_agent;
        lui_out += ' + ' + format_raw_to_price(parseInt(bid_increment)*parseInt(lui_data[3]))
        lui_out += ' + ' + calc_counter_from_time(lui_data[2]);
      }
    }
  }

  if (lui_out != "") {
    el = $(lui_div);
    if (el) {
      if (_lui_running) {
        window.clearTimeout(_lui_running);
      }
      el.innerHTML = lui_out;
      _last_action_div_displayed = 1;
      new Effect.BlindDown(el);
      _lui_running = window.setTimeout('new Effect.BlindUp(\'' + lui_div + '\', {duration:1}); _last_action_div_displayed = 0',4500);
    }
  }
}

function parse_bid_history(bh, auction_id) {

  bh_data = bh.split('#');
  history_out = "";
  own_history_out = "";
  tmp_bh = new Array();
  tmp_own_bh = new Array();
  y = 0;
  z = 0;
  tmp_min_bid_key = 9999999;
  div_stats_all = 'stats_test';
  div_stats_own = 'my_placed_bids';
  if (!document.getElementById(div_stats_all)) {
    return;
  }
  var _tmp_array = [];
  for (x = 0; x < bh_data.length; x++) {
    if (null != bh_data[x]) {
      single_entry = bh_data[x].split(':');

      if (null == single_entry || single_entry.length == 0) {
        continue;
      }

      if (single_entry[0] == null || single_entry[0] == "") {
        continue;
      }

      if (parseInt(single_entry[0]) > parseInt(_last_bid_key)) {
        _last_bid_key = single_entry[0];
      }

      var bidder = new Object();
      bidder["bidder_name"] = single_entry[1];
      bidder["bid_time"] = (new Date()).getTime() + _ms_diff;
      bidder["is_bidder"] = 0;

      tmp_bh[y] = new Object();
      tmp_bh[y]["bid_key"] = parseInt(single_entry[0]);
      tmp_bh[y]["user"] = single_entry[1];
      tmp_bh[y]["type"] = single_entry[2];
      tmp_bh[y]["price"] = format_raw_to_price(single_entry[3]);
      tmp_bh[y]["your_bid"] = 0;
      if (undefined != single_entry[4]) {
        tmp_bh[y]["your_bid"] = single_entry[4];
      }
      if (tmp_bh[y]["your_bid"] == 1) {
        tmp_bh[y]["user"] = "<span class=\"greenfont\">" + tmp_bh[y]["user"] + "</span>";
        tmp_bh[y]["price"] = "<span class=\"greenfont\">" + tmp_bh[y]["price"] + "</span>";
        bidder["is_bidder"] = 1;
        tmp_own_bh[z] = tmp_bh[y];
        z++;
      }
      _tmp_array.push(bidder);


      y++;

      if (single_entry[0] != "" && tmp_min_bid_key >= single_entry[0]) {
        tmp_min_bid_key = parseInt(single_entry[0]);
      }
    }

  }

  if (_first_load == 0){
    for(l=_tmp_array.length-1;l>=0;l--){
      addBidder(_tmp_array[l]);
    }
  }
  _first_load = 0;

  if (tmp_bh != null) {
    if (_bid_history != null && _bid_history.length > 0) {
      for (x = 0; x < _bid_history.length; x++) {
        if (y >= 10) continue;

        if (_bid_history[x]["bid_key"] < tmp_min_bid_key) {
          tmp_bh[y] = _bid_history[x];
          y++;
        }
      }
      _bid_history = tmp_bh;
    } else {
      _bid_history = tmp_bh;
    }
  }

  //get_my_placed_bids(div_stats_own, auction_id);

/*
 *
  if (tmp_own_bh != null) {
    if (_own_bid_history != null && _own_bid_history.length > 0) {
      for (x = 0; x < _own_bid_history.length; x++) {
        if (z >= 10) continue;

        if (_own_bid_history[x]["bid_key"] < tmp_min_bid_key) {
          tmp_own_bh[z] = _own_bid_history[x];
          z++;
        }
      }
      _own_bid_history = tmp_own_bh;
    } else {
      _own_bid_history = tmp_own_bh;
    }
  }
*/
  if (_bid_history != null) {
    history_out += '<table width="220" border="0" cellspacing="0" cellpadding="0">';
    history_out += '<tr> ' +
                   '  <td width="65"></td>' +
                   '  <td width="90"></TD>' +
                   '  <td width="65"></TD>' +
                   '</tr>';

    for (x = 0; x < _bid_history.length; x++) {
      if (x >= 9) continue;
      if (_bid_out_types[_bid_history[x]["type"]] != null) {
        if(_bid_history[x]["your_bid"] == 1){
        _type = "<span class=\"greenfont\">" + _bid_out_types[_bid_history[x]["type"]] + "</span>";
        } else {
        _type = _bid_out_types[_bid_history[x]["type"]];
        }
      } else {
        _type = 'n/a';
      }

      if(x==0){
        history_out += "<tr style=\"font-weight: bold\">";
      }else {
        history_out += "<tr>";
      }
/*
      history_out += "<tr>";
*/
      history_out += "<td align=\"left\">" + _bid_history[x]["price"] + "</td>";
      history_out += "<td>" + _bid_history[x]["user"] + "</td>";
      history_out += "<td>" + _type + "</td>";
      history_out += "</tr>";
/*
      history_out += "type: " + _bid_history[x]["type"];
      history_out += " <br> ";
*/
    }
    history_out += '</table>';
  }

  if (history_out != "") {
    $(div_stats_all).innerHTML = history_out;
  }

/*
  if (_own_bid_history != null) {
    own_history_out += '<table width="220" border="0" cellspacing="0" cellpadding="0">';
    own_history_out += '<tr> ' +
                   '  <td width="60" height="5"></td>' +
                   '  <td width="100"></TD>' +
                   '  <td width="60"></TD>' +
                   '</tr>';

    for (x = 0; x < _own_bid_history.length; x++) {
      if (_bid_out_types[_own_bid_history[x]["type"]] != null) {
        _type = _bid_out_types[_own_bid_history[x]["type"]];
      } else {
        _type = 'n/a';
      }
      own_history_out += "<tr>";
      own_history_out += "<td align=\"center\">" + _own_bid_history[x]["price"] + "</td>";
      own_history_out += "<td>" + _own_bid_history[x]["user"] + "</td>";
      own_history_out += "<td>" + _type + "</td>";
      own_history_out += "</tr>";
    }
    own_history_out += '</table>';
  }


  if (own_history_out != "") {
    $(div_stats_own).innerHTML = own_history_out;
  }
*/
  return;
}

function get_my_placed_bids(div_stats_own, auction_id) {
  if ($(div_stats_own)) {
    new Ajax.Updater(div_stats_own, '/ajax/my_placed_bids.html?aid=' + auction_id);
  }
}

var _last_savings_update = 0;
function update_savings_details(auction_id, force_update) {
  if (auction_id == undefined || auction_id == null) {
    return;
  }

  if(force_update  == undefined || force_update == 0){
    if (((new Date()).getTime()-_last_savings_update)<10000) {
      return;
    }
  }

  _last_savings_update = (new Date()).getTime();

  url = '/ajax/savings.html?aid=' + auction_id;
  new Ajax.Request(url, {onSuccess: function(transport) {
                                      parse_savings_detail(transport);
                                    }
                        });
}

function _calc_savings_detail_by_params() {
  div_name_savings = 'user_savings';

  _savings = parseInt(_savings_ajp_price_sale);

  if (_savings_ajp_is_fixed == 1) {
    __current_price = _savings_ajp_current_price;
  } else {
    __current_price = _savings_ajp_current_price;
    if (_ct_counter_price != undefined && _ct_counter_price != null && _ct_counter_price != '') {
      __current_price = parseInt(_ct_counter_price);
    }
  }
  _savings -= __current_price;

  if (_savings_ajp_own_bids_num > 0) {
    _savings -= (_savings_ajp_own_bids_num*60);
  }

  if (_savings < 0) {
    _savings = 0;
  }

  if ($(div_name_savings)) {
    $(div_name_savings).innerHTML = format_raw_to_price(_savings);
  }
}


function parse_savings_detail(transport) {
  result_array = transport.responseText.split("|");
  div_name_own_bids = 'a_bid_amount_placed';
  div_name_savings = 'user_savings';
  div_name_savings_num = 'user_savings_num_bids';
  div_name_discount_sum = 'user_discount_sum';
  div_name_buy_price = 'buy_it_now_price';
  div_name_savings_num_bin = 'user_savings_num_bids_bin';
  div_name_buy_price_sum = 'buy_it_now_price_sum';
  div_name_freebids_num = 'user_freebids_line';
  offer = $('bid_block_offer');
  info = $('bid_block_info');
  if (result_array.length == 9) {
    el = $(div_name_own_bids);
    el2 = $(div_name_savings);
    el3 = $(div_name_savings_num);
    el4 = $(div_name_discount_sum);
    el5 = $(div_name_buy_price);
    el6 = $(div_name_savings_num_bin);
    el7 = $(div_name_buy_price_sum);
    el8 = $(div_name_freebids_num);
    num_bids = result_array[1];
    pbids = result_array[8];

    if (el) {
      el.innerHTML = result_array[2];
    }
    if (el2) {
      el2.innerHTML = result_array[0];
    }
    if (el3) {
      _savings_ajp_own_bids_num = pbids;
      el3.innerHTML = result_array[1];
    }
    if (el4){
      el4.innerHTML = result_array[3];
    }
    if (el5){
      el5.innerHTML = result_array[4];
    }
    if (el6){
      el6.innerHTML = result_array[1];
      if(num_bids == "1" && (4==4 || 4==5)){
         window.location.reload();
      }
    }
    if (el7){
      el7.innerHTML = result_array[5];
    }
    if (el8){
      if(result_array[7] > 0) {
        el8.style.display = "inline";
        if($('tip_bin_freebids')){
          $('tip_bin_freebids').style.display = "inline";
        }
        if($('user_num_freebids')){
          $('user_num_freebids').innerHTML = result_array[7];
        }
      }
    }


  } else if (result_array.length == 1) {
    el2 = $(div_name_savings);
    if (el2) {
      el2.innerHTML = result_array[0];
    }
  }
}


var updated_to_cds = 0;
function _update_counter_speed_image(_sec, _num_bids, _current_sec) {
  if (undefined == bid_increment || null == bid_increment) {
    bid_increment = _default_bid_increment;
  }

  if (_last_update_ct_time == _current_sec) {
//    return;
  }
  updated_to_cds = _current_sec;

  _last_update_ct_time = bid_increment;

  _price = format_raw_to_price(parseInt(_num_bids)*parseInt(bid_increment));
  _text = '' + _sec + ' second countdown<br/>starts at ' + _price;
  _img = 'http://img2.swoopo.com/telebid-us/img/countdown/uhr' + _current_sec + '.png';
  el = $('CDUMST');
  if (el) {
    el.innerHTML = _text;
  }
  el = $('CDUMST_IMG');
  if (el) {
    el.src = _img;
  }
}

function _delete_counter_speed_image(_current_sec) {
  el = $('CDUMST');
  if (el) {
    el.parentNode.removeChild(el);
  }

  if (_cds_image_deleted == 1) {
    return;
  }
  _cds_image_deleted = 1;
  _img = 'http://img2.swoopo.com/telebid-us/img/countdown/uhr' + _current_sec + '.png';
  el = $('CDUMST_IMG');
  if (el) {
    el.src = _img;
  }
}
