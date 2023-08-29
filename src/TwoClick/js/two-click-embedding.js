(function ($, Drupal, once) {
  function prepareReplaceTwoClickWithVideoEmbed(container) {
    const $button = $('.two-click__button-container', container);
    $button.on('click', () => {
      replaceTwoClickWithVideoEmbed(container)
    });
  }

  function replaceTwoClickWithVideoEmbed(container) {
    const $embed = $(container.dataset.embed);
    const $twoClickContainer = $('.two-click__container', container);

    $embed.outerWidth("100%");

    const $disclaimerContainer = $(container).find('.two-click__disclaimer');
    $disclaimerContainer.find('input[type="checkbox"]').prop("checked", true);

    container.dataset.embed = $twoClickContainer.prop('outerHTML');
    $twoClickContainer.replaceWith($embed);
  }

  function replaceVideoWithTwoClickEmbed(container) {
    const $embed = $(container.dataset.embed);
    const $container = $(container);
    const $iframe = $container.find('iframe');

    const $disclaimerContainer = $container.find('.two-click__disclaimer');
    $disclaimerContainer.find('input[type="checkbox"]').prop("checked", false);

    container.dataset.embed = $iframe.prop('outerHTML') ?? "";
    if ($iframe.length === 0) $container.prepend($embed);

    $iframe.replaceWith($embed);
    prepareReplaceTwoClickWithVideoEmbed(container)

  }

  Drupal.behaviors.grid_two_click_embedding = {
    attach: function (context, settings) {

      const elements = once('two-click', '.two-click', context);

      if (!elements.length) return;

      elements.forEach((element) => {
        prepareReplaceTwoClickWithVideoEmbed(element)
        const $disclaimerContainer = $(element).find('.two-click__disclaimer');

        $disclaimerContainer.on('change', (event) => {

          const checked = $(event.currentTarget).find('input[type=checkbox]')[0].checked

          if (!checked) replaceVideoWithTwoClickEmbed(element)
          else replaceTwoClickWithVideoEmbed(element)
        });
      });
    }
  }
})(jQuery, Drupal, once);
