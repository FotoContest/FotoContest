!function($, window, document, _undefined)
{
  $(document).ready(function(){
    var primaryImage = $('#primaryImage');

    $('.contestEntryThumbs').on('click', 'img', function(e){
      var target = $(e.target);
      primaryImage.attr('src', target.data('src'));
      e.preventDefault();
    });
  });
}
(jQuery, this, document);
