/* global Shepherd, _, wu_tours, ajaxurl, wu_tours_vars */
(function($) {

  $(document).ready(function() {

    _.each(wu_tours, function(tour, tour_id) {

      window[tour_id] = new Shepherd.Tour({
        useModalOverlay: true,
        includeStyles: false,
        styleVariables: {
          arrowSize: 1.1,
        },
        defaultStepOptions: {
          classes: 'wu-p-2 wu-bg-white wu-shadow-sm wu-rounded wu-text-left wu-text-gray-700',
          scrollTo: {
            block: 'center',
            behavior: 'smooth',
          },
          tippyOptions: {
            zIndex: 999999,
            onCreate(instance) {

              instance.popper.classList.add('wu-styling');

              const elements = instance.popperChildren.content.children[0].children[0].children;

              if (elements[0].children[0]) {

                elements[0].children[0].classList.add('wu-p-2', 'wu-pb-0', 'wu-m-0', 'wu--mb-1', 'wu-text-gray-800');

              } // end if;

              elements[1].classList.add('wu-p-2');

              elements[2].classList.add('wu--mt-1', 'wu-p-2', 'wu-bg-gray-200', 'wu-rounded', 'wu-text-right');

            },
          },
        },
      });

      window[tour_id].on('complete', function() {

        $.ajax({
          url: ajaxurl,
          data: {
            action: 'wu_mark_tour_as_finished',
            tour_id,
            nonce: wu_tours_vars.nonce,
          },
        });

      });

      _.each(tour, function(step) {

        window[tour_id].addStep({
          ...step,
          buttons: [
            {
              classes: 'button button-primary wu-text-xs sm:wu-normal-case',
              text: wu_tours_vars.i18n.next,
              action: window[tour_id].next,
            },
          ],
        });

      });

      window[tour_id].start();

    });

  });

}(jQuery));
