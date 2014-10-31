//general setup stuff
var ifTabs = jQuery('body').find('.wiki-embed-tabs').length;
var ifAccordion = jQuery('body').find('.wiki-embed-accordion').length;

/**
 * this section is to handle clicks to internal anchors
 */
if (ifTabs || ifAccordion) {
  jQuery('.wiki-embed a').not('#toc a').each(function() {
    jQuery(this).on('click', function() {
     //try to get hash of url
      var hash = jQuery(this).attr('href').split('#')[1];
      if (typeof hash !== "undefined") {
        //try to make sure that we can find the link internally on the same page
        var try_link = jQuery('[name="'+hash.replace('#','')+'"]');
        if (!try_link.length) {
          try_link = jQuery('#'+hash.replace('#',''));
        }

        //ok, we found the link, we need to open the correct tab, then scroll to it
        if (try_link.length) {
          var tab_of_hash = try_link.parents('.wikiembed-fragment').attr('id');
          if (typeof tab_of_hash !== 'undefined') {
            jQuery('a[href="#'+tab_of_hash+'"]').click();
            scrollToAnchor(hash);
          }
        }
      }
    });
  });
}

/**
 * this section is to handle clicks if there is a TOC for both tabs and accordion
 */
if (jQuery('#toc').length) {
  if (ifTabs) {
    jQuery('#toc a').on('click', function() {
      id = jQuery(this).attr('href');
      tab_id = jQuery('.tab-content').find(id).parents('.wikiembed-fragment').attr('id');
      selector = 'a[href="#'+tab_id+'"]';
      jQuery(selector).click();
    });
  }
  
  if (ifAccordion) {
    //we might need to cheat to make it so that level 1 toc heading has ninja anchor
    jQuery('#toc li.toclevel-1 > a').each(function() {
      var id = jQuery('span.toctext', this).text();
      var href = jQuery(this).attr('href');
      var hidden_anchor = jQuery('<span id="'+href.replace('#','')+'" style="color: transparent;"></span>');
    
      jQuery('.wiki-embed-accordion .accordion-heading a').filter(function() {
        return jQuery(this).text() == id;
      }).parent().append(hidden_anchor);
    });
    
    //clicking
    jQuery('#toc a').on('click', function() {
      href_id = jQuery(this).attr('href');
      id = jQuery('span.toctext', this).text();
      
      if (!jQuery(this).parent().hasClass('toclevel-1')) {
        id = jQuery(this).parents('li.toclevel-1').children('a').children('span.toctext').text();
      }
      
      var acc_id = jQuery('.wiki-embed-accordion .accordion-heading a').filter(function() {
        return jQuery(this).text() == id;
      }).attr('href');
      
      //reset all accordion bits
      jQuery('.wiki-embed-accordion a.accordion-toggle').addClass('collapsed');
      jQuery('.wiki-embed-accordion .wikiembed-fragment-accordion').removeClass('in').css('height', '0px');
    
      //select the right accordion bit to open up
      var selector = 'a[href="'+acc_id+'"]';
      jQuery(selector).click();	//open accordion section
      scrollToAnchor(href_id);
    });
  }
}

/**
 * function to smoothly scroll to appropritae tag (currently for TOC)
 *
 */
function scrollToAnchor(aid){
  var aTag = '';jQuery(".wiki-embed-accordion a[href='"+ aid +"']");
  if (!aTag.length) {
    aTag = jQuery('#'+aid.replace('#',''));
  }
  if (!aTag.length) {
    aTag = jQuery('[name="'+aid.replace('#','')+'"]');
  }
  if (aTag.length) {
    jQuery('html,body').animate({scrollTop: aTag.offset().top},'fast');
  }
}
