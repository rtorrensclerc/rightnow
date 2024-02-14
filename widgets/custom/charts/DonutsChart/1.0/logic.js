RightNow.namespace('Custom.Widgets.charts.DonutsChart');
Custom.Widgets.charts.DonutsChart = RightNow.Widgets.extend({

  /**
   * Contructor del Widget
   */
  constructor: function () {

    // Variables
    window.DonutsChart = this;

    // Precarga de base de mensajes
    // RightNow.Interface.getMessage("CUSTOM_MSG_XXX");

    this.init();
  },

  /**
   * Método inicial
   */
  init: function () {

    // Mapeo de elementos del DOM
    this.widget = this.Y.one(this.baseSelector);
    this.svg = this.widget.one('svg');
    this.data.js._data = [{
      "period": "01-0000",
      "data": [{
        "percentage": 20,
        "counter_type": ''
      }, {
        "percentage": 20,
        "counter_type": ''
      }, {
        "percentage": 20,
        "counter_type": ''
      }]
    }];

    // Instancias
    // this.contract_list = Integer.getInstanceByName('contract_list');

    // Carga Listas
    // Integer.appendOptions(this.contract_list, this.data.js.list.contracts, 'select', null, true);

    // Subscipción de eventos
    RightNow.Event.subscribe("evt_LoadDataDonutsChart", this.loadDataDonutsChart, this);

    // Eventos
    // this.contract_list.input.on('change', this.changeContract, this);
    this.loadChart();

  },

  /**
   * 
   */
  loadDataDonutsChart: function (evt, arr_args) {
    this.data.js._data = (arr_args[0]) ? arr_args[0] : this.data.js._data;
    this.data.js.period = (arr_args[1]) ? arr_args[1] : this.data.js._data.length - 1;
    this.loadChart(this.data.js.period);
  },

  loadChart: function (period_index) {
    period_index = period_index || this.data.js._data.length - 1;

    // Map de totales
    this.total_arrays = {};
    this.arr_totals = [];

    for (value in this.data.js._data) {
      this.total_arrays[this.data.js._data[value].period] = [];
      var arr_subtotal = [];

      for (var i = 0, cant = this.data.js._data[value].data.length; i < cant; i++) {
        this.total_arrays[this.data.js._data[value].period].push(this.data.js._data[value].data[i].percentage);
        arr_subtotal.push(this.data.js._data[value].data[i].percentage);
      }

      this.arr_totals.push(d3.sum(arr_subtotal));
    }

    /* for (value in this.data.js._data) {
      this.total_arrays[this.data.js._data[value].TRX_DATE] = [];
      var arr_subtotal = [];

      this.total_arrays[this.data.js._data[value].TRX_DATE].push(this.data.js._data[value].percentage);
      arr_subtotal.push(this.data.js._data[value].percentage);
      this.arr_totals.push(d3.sum(arr_subtotal));
    } */

    this.bar_container = d3.select(this.baseSelector + ' svg');

    // Determinar el periodo activo

    this.updateBars(this.data.js._data[period_index].data);
    // this.updateBars(this.data.js._data[period_index]);
  },

  /**
   * Crea y Actualiza el gráfico
   */
  updateBars: function (_data) {
    // Variables
    var margin = {
        top: 20,
        right: 20,
        bottom: 20,
        left: 20
      },
      width = 500 - margin.left - margin.right,
      height = 300 - margin.top - margin.bottom;
    radius = Math.min(width, height) / 2;
    donutWidth = 50;
    legendRectSize = 18;
    legendSpacing = 4;
    colors = ['#DB021B', '#DBC902', '#DB8802'];

    // Limpia el SVG
    if (this.donuts_svg) {
      this.svg.setHTML('');
      this.donuts_svg = null;
    }

    // Definición DOM
    this.donuts_svg = d3.select(this.baseSelector + ' svg')
      .attr("preserveAspectRatio", "xMinYMin meet")
      .attr("viewBox", "0 0 " + (width + margin.left + margin.right) + " " + (height + margin.top + margin.bottom))
      .classed("svg-content-responsive", true)
      .classed("svg-container", true)
      .append("g")
      .attr('transform', 'translate(' + (width / 2) + ',' + (height / 2) + ')');

    arc = d3.arc()
      .innerRadius(radius - donutWidth)
      .outerRadius(radius);

    pie = d3.pie()
      .value(function (d) {
        return Number(d.percentage)
      })

    path = this.donuts_svg.selectAll('path')
      .data(pie(_data))
      .enter()
      .append('g')
    
    path.append('path')
    .attr('d', arc)
    .attr('fill', function (d, i) {
      return colors[i]
    })

    path.append('text')
      .attr('transform', function (d) {
        d.innerRadius = 0;
        // d.outerRadius = r;
        return 'translate(' + arc.centroid(d) + ')';
      })
      .attr('text-anchor', 'middle')
      .text(function (d) {
        return d.value;
      })


    var legend = this.donuts_svg.selectAll('.legend')
      .data(_data)
      .enter()
      .append('g')
      .attr('class', 'legend')
      .attr('transform', function (d, i) {
        var height = 18;
        var offset = height / 2;
        var horz = -3 * 20;
        var vert = i * height - offset;
        return 'translate(' + horz + ',' + vert + ')';
      });

    legend.append('rect')
      .attr('width', 20)
      .attr('height', 20)
      .style('fill', function (d, i) {
        return colors[i]
      })

    legend.append('text')
      .attr('x', legendRectSize + legendSpacing)
      .attr('y', legendRectSize - legendSpacing)
      .text(function (d) {
        return d.counter_type;
      });
  }
});

