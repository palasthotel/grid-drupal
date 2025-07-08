(function ($, Drupal, once) {
  function prepareReplaceTwoClickWithEmbed(container) {
    const $button = $('.two-click__button-container', container);
    $button.on('click', (event) => {
      const playButton = event.currentTarget
      const parent = playButton.closest('.two-click')
      const disclaimer = parent.querySelector('.two-click__disclaimer')

      // if disclaimer is on screen, we enable it via a change event on the input of the disclaimer
      if (disclaimer.classList.contains('show')) {
        disclaimer.querySelector('input[type=checkbox]').checked = true
        setTimeout( () => {
          event = new Event('change');
          disclaimer.dispatchEvent(event); //
        }, 250)  //add some delay for the animation
      }
      else disclaimer.classList.add('show') // first we show the disclaimer
    });


  }

  function replaceTwoClickWithEmbed(container) {

    const embed = container.dataset.embed === container.dataset.originalEmbed ? container.dataset.embed : container.dataset.originalEmbed
    const $embed = $(atob(embed));
    const $twoClickContainer = $('.two-click__container', container);

    $embed.outerWidth("100%");

    const $disclaimerContainer = $(container).find('.two-click__disclaimer');
    $disclaimerContainer.find('input[type="checkbox"]').prop("checked", true);

    container.dataset.embed = $twoClickContainer.prop('outerHTML');
    $twoClickContainer.replaceWith($embed);

    const iframeEmbeddedEvent = new CustomEvent("two-click-iframe-embedded", {
      detail: {twoClickContainer: container},
      bubbles: true,
      cancelable: true,
      composed: false,
    });
    document.dispatchEvent(iframeEmbeddedEvent);
  }

  function replaceEmbedWithTwoClick(container) {
    const embed = container.dataset.embed;
    const $embed = $(embed);
    const $container = $(container);
    const $iframe = $container.find('iframe');

    const $disclaimerContainer = $container.find('.two-click__disclaimer');
    $disclaimerContainer.find('input[type="checkbox"]').prop("checked", false);

    container.dataset.embed = btoa($iframe.prop('outerHTML')) ?? "";
    if ($iframe.length === 0) $container.prepend($embed);

    $iframe.replaceWith($embed);
    prepareReplaceTwoClickWithEmbed(container)

    const iframeReplacedEvent = new CustomEvent("two-click-iframe-replaced", {
      detail: {twoClickContainer: container},
      bubbles: true,
      cancelable: true,
      composed: false,
    });
    document.dispatchEvent(iframeReplacedEvent);
  }

  Drupal.behaviors.grid_two_click_embedding = {
    attach: function (context, settings) {

      const elements = once('two-click', '.two-click', context);
      if (!elements.length) return;

      elements.forEach((element) => {
        prepareReplaceTwoClickWithEmbed(element)
        const $disclaimerContainer = $(element).find('.two-click__disclaimer');

        $disclaimerContainer.on('change', (event) => {
          const input = $(event.currentTarget).find('input[type=checkbox]')[0]
          const checked =  input.checked


          setTimeout( () => {
            $disclaimerContainer.toggleClass('show')
            $disclaimerContainer.toggleClass('disclaimer-clicked')

            if (!checked) replaceEmbedWithTwoClick(element)
            else replaceTwoClickWithEmbed(element)
          }, 250) //add some delay for the animation


        });
      });
    }
  }

  const registerVideoControlsHandler = (element) => {

    element.querySelector('.close')?.addEventListener('click', (event) => {
      element.classList.remove('is-hovering')
      element.classList.add('in-grid')
      element.classList.add('is-closed')
      const twoClickContainer = element.querySelector('.two-click')
      if (twoClickContainer.classList.contains('has-embed')) replaceEmbedWithTwoClick(twoClickContainer) // stop the video by force

    })
  }


  Drupal.behaviors.grid_video_hover_box = {
    attach: function (context, settings) {

      const elements = once('grid-video-hover-box', '.grid-box-video .in-grid', context);
      if (!elements.length) return;

      const firstGridBox = elements[0].closest('.grid-box-video')


      const options = {
        rootMargin: "0px",
      };

      const handleIntersect = (entries, observer) => {
        entries.forEach((entry) => {

          const element = entry.target
          registerVideoControlsHandler(element)

          if (element.classList.contains('is-closed')) return

          if (entry.isIntersecting) {
            //Elvis has ENTERED the building
            element.classList.add('in-grid')
            element.classList.remove('is-hovering')
          } else {
            //Elvis has LEFT the building
            element.classList.add('is-hovering')
            element.classList.remove('in-grid')
            element.classList.remove('is-cinema')
          }
        });
      }

      const observer = new IntersectionObserver(handleIntersect, options);
      const target = firstGridBox.querySelector(".video-hover.hover-active");

      observer.observe(target);

      document.addEventListener('two-click-iframe-embedded', (event) => {
        const twoClickContainer = event.detail.twoClickContainer
        const container = twoClickContainer.closest('.video-container')
        if (!container) return;
        container.classList.add('playing')

      });

      document.addEventListener('two-click-iframe-replaced', (event) => {
        const twoClickContainer = event.detail.twoClickContainer
        const container = twoClickContainer.closest('.video-container')
        if (!container) return;
        container.classList.remove('playing')
      });

    }
  }
})(jQuery, Drupal, once);
