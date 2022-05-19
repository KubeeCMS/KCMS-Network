/* eslint-disable */
(function() {

  let graph = document.getElementById('wp-ultimo-taxes');

  if (!graph) {

    return;

  } // end if;

  const tax_graph = new Vue({
    el: '#wp-ultimo-taxes',
    components: {
      apexchart: window.VueApexCharts,
    },
    data: {
      chart_options: {
        mrr_growth: {
          series: [
            {
              name: wu_tax_statistics_vars.i18n.net_profit_label,
              data: [
                wu_tax_statistics_vars.data.january.net_profit,
                wu_tax_statistics_vars.data.february.net_profit,
                wu_tax_statistics_vars.data.march.net_profit,
                wu_tax_statistics_vars.data.april.net_profit,
                wu_tax_statistics_vars.data.may.net_profit,
                wu_tax_statistics_vars.data.june.net_profit,
                wu_tax_statistics_vars.data.july.net_profit,
                wu_tax_statistics_vars.data.august.net_profit,
                wu_tax_statistics_vars.data.september.net_profit,
                wu_tax_statistics_vars.data.october.net_profit,
                wu_tax_statistics_vars.data.november.net_profit,
                wu_tax_statistics_vars.data.december.net_profit
              ]
            },
            {
              name: wu_tax_statistics_vars.i18n.taxes_label,
              data: [
                wu_tax_statistics_vars.data.january.tax_total,
                wu_tax_statistics_vars.data.february.tax_total,
                wu_tax_statistics_vars.data.march.tax_total,
                wu_tax_statistics_vars.data.april.tax_total,
                wu_tax_statistics_vars.data.may.tax_total,
                wu_tax_statistics_vars.data.june.tax_total,
                wu_tax_statistics_vars.data.july.tax_total,
                wu_tax_statistics_vars.data.august.tax_total,
                wu_tax_statistics_vars.data.september.tax_total,
                wu_tax_statistics_vars.data.october.tax_total,
                wu_tax_statistics_vars.data.november.tax_total,
                wu_tax_statistics_vars.data.december.tax_total
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
            colors: ['#2c3e50', '#95a5a6'],
            plotOptions: {
              bar: {
                horizontal: false,
                columnWidth: '40%',
                endingShape: 'rounded',
                startingShape: 'rounded',
              },
            },
            xaxis: {
              categories: wu_tax_statistics_vars.month_list,
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

