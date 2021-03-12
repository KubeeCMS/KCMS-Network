/* eslint-disable */
(function() {

  let graph = document.getElementById('wp-ultimo-mrr-growth');

  if (!graph) {

    return;

  } // end if;

  const mrr_graph = new Vue({
    el: '#wp-ultimo-mrr-growth',
    components: {
      apexchart: window.VueApexCharts,
    },
    data: {
      start_date: wu_dashboard_statistics_vars.start_date,
      end_date: wu_dashboard_statistics_vars.end_date,
      chart_options: {
        mrr_growth: {
          series: [
            {
              name: wu_dashboard_statistics_vars.i18n.new_mrr,
              data: [
                wu_dashboard_statistics_vars.mrr_array.january.total,
                wu_dashboard_statistics_vars.mrr_array.february.total,
                wu_dashboard_statistics_vars.mrr_array.march.total,
                wu_dashboard_statistics_vars.mrr_array.april.total,
                wu_dashboard_statistics_vars.mrr_array.may.total,
                wu_dashboard_statistics_vars.mrr_array.june.total,
                wu_dashboard_statistics_vars.mrr_array.july.total,
                wu_dashboard_statistics_vars.mrr_array.august.total,
                wu_dashboard_statistics_vars.mrr_array.september.total,
                wu_dashboard_statistics_vars.mrr_array.october.total,
                wu_dashboard_statistics_vars.mrr_array.november.total,
                wu_dashboard_statistics_vars.mrr_array.december.total
              ]
            },
            {
              name: wu_dashboard_statistics_vars.i18n.cancellations,
              data: [
                -wu_dashboard_statistics_vars.mrr_array.january.cancelled,
                -wu_dashboard_statistics_vars.mrr_array.february.cancelled,
                -wu_dashboard_statistics_vars.mrr_array.march.cancelled,
                -wu_dashboard_statistics_vars.mrr_array.april.cancelled,
                -wu_dashboard_statistics_vars.mrr_array.may.cancelled,
                -wu_dashboard_statistics_vars.mrr_array.june.cancelled,
                -wu_dashboard_statistics_vars.mrr_array.july.cancelled,
                -wu_dashboard_statistics_vars.mrr_array.august.cancelled,
                -wu_dashboard_statistics_vars.mrr_array.september.cancelled,
                -wu_dashboard_statistics_vars.mrr_array.october.cancelled,
                -wu_dashboard_statistics_vars.mrr_array.november.cancelled,
                -wu_dashboard_statistics_vars.mrr_array.december.cancelled
              ]
            },
          ],
          chartOptions: {
            chart: {
              type: 'bar',
              height: 300,
              stacked: true,
              toolbar: {
                show: false
              },
              zoom: {
                enabled: true
              }
            },
            dataLabels: {
              enabled: false,
              maxItems: 0,
            },
            responsive: [{
              breakpoint: 480,
              options: {
                legend: {
                  position: 'bottom',
                  offsetX: -10,
                  offsetY: 0
                }
              }
            }],
            colors: ['#3498db', '#e74c3c'],
            plotOptions: {
              bar: {
                horizontal: false,
                columnWidth: '40%',
                endingShape: 'rounded',
                startingShape: 'rounded',
              },
            },
            xaxis: {
              categories: wu_dashboard_statistics_vars.month_list,
              position: 'bottom',
              axisBorder: {
                show: true
              },
              axisTicks: {
                show: true
              },
              crosshairs: {
                fill: {
                  type: 'gradient',
                  gradient: {
                    colorFrom: '#D8E3F0',
                    colorTo: '#BED1E6',
                    stops: [0, 100],
                    opacityFrom: 0.4,
                    opacityTo: 0.5,
                  }
                }
              },
              tooltip: {
                enabled: true,
              }
            },
            yaxis: {
              labels: {
                formatter: function (y) {
                  return y >= 0 ? wu_format_money(y) : '-' + wu_format_money(y);
                }
              }
            },
            legend: {
              position: 'top',
              offsetY: 0
            },
            fill: {
              opacity: 1
            }
          },
        },
      },
    },
  });
}());

(function($) {
  $(document).ready(function() {

    $('.wu-loader').on('click', function() {

      wu_block_ui('#wpcontent');

    });

    $('#wu-date-range').flatpickr({
      mode: "range",
      dateFormat: "Y-m-d",
      maxDate: wu_dashboard_statistics_vars.today,
      defaultDate: [
        wu_dashboard_statistics_vars.start_date,
        wu_dashboard_statistics_vars.end_date,
      ],
      onClose: function(selectedDates) {

        const redirect = new URL(window.location.href);

        redirect.searchParams.set('start_date', moment(selectedDates[0]).format('YYYY-MM-DD'));
        redirect.searchParams.set('end_date', moment(selectedDates[1]).format('YYYY-MM-DD'));

        window.location.href = redirect.toString();

        wu_block_ui('#wpcontent');

      },
    });

  });
})(jQuery);