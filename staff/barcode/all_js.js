
var delay = (function(){
  var timer = 0;
  return function(callback, ms){
    clearTimeout (timer);
    timer = setTimeout(callback, ms);
  };
})();

function log(detail){
  
  $.ajax({
url: "bar.php?cmd=log&id="+detail,
processData: false
});
}
