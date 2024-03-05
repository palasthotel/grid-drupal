(function ($, Drupal, once) {
  function prepareReplaceTwoClickWithEmbed(container) {
    const $button = $('.two-click__button-container', container);
    $button.on('click', () => {
      replaceTwoClickWithEmbed(container)
    });


  }

  function replaceTwoClickWithEmbed(container) {

    const embed = container.dataset.embed === container.dataset.originalEmbed ? container.dataset.embed : container.dataset.originalEmbed
    const $embed = $(embed);
    const $twoClickContainer = $('.two-click__container', container);

    $embed.outerWidth("100%");

    const $disclaimerContainer = $(container).find('.two-click__disclaimer');
    $disclaimerContainer.find('input[type="checkbox"]').prop("checked", true);

    container.dataset.embed = $twoClickContainer.prop('outerHTML');
    $twoClickContainer.replaceWith($embed);

    const iframeEmbeddedEvent = new CustomEvent("two-click-iframe-embedded", {
      detail: {},
      bubbles: true,
      cancelable: true,
      composed: false,
    });
    document.dispatchEvent(iframeEmbeddedEvent);


  }

  function replaceEmbedWithTwoClick(container) {
    const $embed = $(container.dataset.embed);
    const $container = $(container);
    const $iframe = $container.find('iframe');

    const $disclaimerContainer = $container.find('.two-click__disclaimer');
    $disclaimerContainer.find('input[type="checkbox"]').prop("checked", false);

    container.dataset.embed = $iframe.prop('outerHTML') ?? "";
    if ($iframe.length === 0) $container.prepend($embed);

    $iframe.replaceWith($embed);
    prepareReplaceTwoClickWithEmbed(container)

  }

  Drupal.behaviors.grid_two_click_embedding = {
    attach: function (context, settings) {

      const elements = once('two-click', '.two-click', context);

      if (!elements.length) return;

      elements.forEach((element) => {
        prepareReplaceTwoClickWithEmbed(element)
        const $disclaimerContainer = $(element).find('.two-click__disclaimer');

        $disclaimerContainer.on('change', (event) => {

          const checked = $(event.currentTarget).find('input[type=checkbox]')[0].checked

          if (!checked) replaceEmbedWithTwoClick(element)
          else replaceTwoClickWithEmbed(element)
        });
      });
    }
  }
})(jQuery, Drupal, once);
