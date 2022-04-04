(function ($, Drupal) {
  Drupal.behaviors.grid_two_click_embedding = {
    attach: function (context, settings) {

      $('.two-click__container', context).once('grid-two-click').each((index, container) => {
        const $container = $(container);
        const $button = $container.find('.two-click__button-container')
        $button.on('click', () => {

          const $videoembed = $(container.dataset.videoembed);
          $videoembed.outerHeight($container.outerHeight());
          $videoembed.outerWidth("100%");

          $container.replaceWith($videoembed);

        });
      });
    }
  }
})(jQuery, Drupal);
