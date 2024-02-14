RightNow.namespace('Custom.Widgets.charts.BarChart');
Custom.Widgets.charts.BarChart = RightNow.Widgets.extend({

  /**
   * Contructor del Widget
   */
  constructor: function () {

    // Variables
    window.BarChart = this;

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
        "percentage": 0
      }]
    }, {
      "period": "02-0000",
      "data": [{
        "percentage": 0
      }]
    }, {
      "period": "03-0000",
      "data": [{
        "percentage": 0
      }]
    }, {
      "period": "04-0000",
      "data": [{
        "percentage": 0
      }]
    }, {
      "period": "05-0000",
      "data": [{
        "percentage": 0
      }]
    }, {
      "period": "06-0000",
      "data": [{
        "percentage": 0
      }]
    }];

    // this.data.js._data = [{
    //   "period": "11-2017",
    //   "data": [{
    //     "counter_type": "Copias B\/N",
    //     "quantity": "452",
    //     "percentage": "10.84",
    //     "invoice": "256659"
    //   }, {
    //     "counter_type": "Copias Color",
    //     "quantity": "3718",
    //     "percentage": "89.16",
    //     "invoice": "256659"
    //   }]
    // }, {
    //   "period": "03-2018",
    //   "data": [{
    //     "counter_type": "Copias B\/N",
    //     "quantity": "1286",
    //     "percentage": "29.92",
    //     "invoice": "281176"
    //   }, {
    //     "counter_type": "Copias Color",
    //     "quantity": "3012",
    //     "percentage": "70.08",
    //     "invoice": "281176"
    //   }]
    // }, {
    //   "period": "02-2018",
    //   "data": [{
    //     "counter_type": "Copias B\/N",
    //     "quantity": "260",
    //     "percentage": "10.69",
    //     "invoice": "277720"
    //   }, {
    //     "counter_type": "Copias Color",
    //     "quantity": "2172",
    //     "percentage": "89.31",
    //     "invoice": "277720"
    //   }]
    // }, {
    //   "period": "12-2017",
    //   "data": [{
    //     "counter_type": "Copias B\/N",
    //     "quantity": "262",
    //     "percentage": "7.41",
    //     "invoice": "262857"
    //   }, {
    //     "counter_type": "Copias Color",
    //     "quantity": "3276",
    //     "percentage": "92.59",
    //     "invoice": "262857"
    //   }]
    // }, {
    //   "period": "10-2017",
    //   "data": [{
    //     "counter_type": "Copias B\/N",
    //     "quantity": "636",
    //     "percentage": "24.05",
    //     "invoice": "241527"
    //   }, {
    //     "counter_type": "Copias Color",
    //     "quantity": "2008",
    //     "percentage": "75.95",
    //     "invoice": "241527"
    //   }]
    // }, {
    //   "period": "01-2018",
    //   "data": [{
    //     "counter_type": "Copias B\/N",
    //     "quantity": "806",
    //     "percentage": "13.53",
    //     "invoice": "271570"
    //   }, {
    //     "counter_type": "Copias Color",
    //     "quantity": "5152",
    //     "percentage": "86.47",
    //     "invoice": "271570"
    //   }]
    // }];

    // Instancias
    // this.contract_list = Integer.getInstanceByName('contract_list');

    // Carga Listas
    // Integer.appendOptions(this.contract_list, this.data.js.list.contracts, 'select', null, true);

    // Subscipción de eventos
    RightNow.Event.subscribe("evt_LoadDataBarChart", this.loadDataBarChart, this);

    // Eventos
    // this.contract_list.input.on('change', this.changeContract, this);

    this.loadChart();
  },

  /**
   * 
   */
  loadDataBarChart: function (evt, arr_args) {
    this.data.js._data = arr_args[0].detail;
    this.loadChart();
  },

  loadChart: function () {

    // Map de totales
    this.total_arrays = {};
    this.arr_totals = [];

    /* for (value in this.data.js._data) {
      this.total_arrays[this.data.js._data[value].period] = [];
      var arr_subtotal = [];

      for (var i = 0, cant = this.data.js._data[value].data.length; i < cant; i++) {
        this.total_arrays[this.data.js._data[value].period].push(this.data.js._data[value].data[i].quantity);
        arr_subtotal.push(this.data.js._data[value].data[i].quantity);
      }

      this.arr_totals.push(d3.sum(arr_subtotal));
    }

    this.bar_container = d3.select(this.baseSelector + ' svg');

    this.updateBars(this.data.js._data); */


    for (value in this.data.js._data) {
      this.total_arrays[this.data.js._data[value].TRX_DATE] = [];
      var arr_subtotal = [];
      this.total_arrays[this.data.js._data[value].TRX_DATE].push(this.data.js._data[value].AMOUNT);
      arr_subtotal.push(this.data.js._data[value].AMOUNT);
      this.arr_totals.push(d3.sum(arr_subtotal));
    }

    this.bar_container = d3.select(this.baseSelector + ' svg');

    this.updateBars(this.data.js._data);

  },

  /**
   * Crea y Actualiza el gráfico
   */
  updateBars: function (_data) {

    // Variables
    var margin = {
        top: 20,
        right: 80,
        bottom: 30,
        left: 100
      },
      width = 500 - margin.left - margin.right,
      height = 300 - margin.top - margin.bottom;

    // this.totales = this.data.js._data.map(function (d) {
    //   return d.total
    // });

    // Escalas de Ejes
    var x = d3.scaleBand().rangeRound([0, width]).padding(.1);
    var y = d3.scaleLinear().range([height, 0]);

    // Ejes
    var xAxis = d3.axisBottom(x)
    .tickFormat(function(d) {
      return d;
    })
    var yAxis = d3.axisLeft(y);

    // Limpia el SVG
    if (this.bar_svg) {
      this.svg.setHTML('');
      this.bar_svg = null;
    }

    // Definición DOM
    this.bar_svg = d3.select(this.baseSelector + ' svg')
      .attr("preserveAspectRatio", "xMinYMin meet")
      .attr("viewBox", "0 0 " + (width + margin.left + margin.right) + " " + (height + margin.top + margin.bottom))
      .classed("svg-content-responsive", true)
      .classed("svg-container", true)
      .append("g")
      .attr("transform", "translate(" + margin.left + "," + margin.top + ")")

    // Dominios
    y.domain([0, d3.max(this.arr_totals)]);
    x.domain(d3.keys(this.total_arrays));

    // Iniciación del Eje X
    this.bar_svg.append("g")
      .attr("class", "x axis")
      .attr("transform", "translate(0," + height + ")")
      .call(xAxis)

    // Iniciación del Eje Y
    this.bar_svg.append("g")
      .attr("class", "y axis")
      .call(yAxis)

    // Barras
    var bars = this.bar_svg.selectAll(".bar")
      .data(d3.entries(this.total_arrays))
      .enter()

    // Rectangulos
    bars.append("rect")
      .attr("y", function (d) {
        return y(d3.sum(d.value))
      })
      .attr("x", function (d, i) {
        return x(d.key);
      })
      .attr("height", function (d, i) {
        return y(0) - y(d3.sum(d.value));
      })
      .attr("width", function (d) {
        return x.bandwidth();
      })
      .attr("class", function(d, i, bars){
        if(i + 1 === bars.length) {
          return 'bar active';
        }
      
        return 'bar';
      })
      .on('click', function () {
        d3.selectAll('.bar').classed('active', false)
        d3.select(this).classed('active', true);
        RightNow.Event.fire('evt_LoadDataDonutsChart', null, arguments[1]);
        RightNow.Event.fire('evt_UpdateLastConsumptionsLines', null, arguments[1]);
      })
      
      // bars.filter(function(d, i){ if(bars.size() === i + 1){ return d } }).attr('class', 'active');

    // Valor inline
    bars.insert("text")
      .attr('y', function (d) {
        return y(d3.sum(d.value)) - 5;
      })
      .attr('x', function (d) {
        return x(d.key) + x.bandwidth() / 2;
      })
      .style('text-anchor', 'middle')
      .style('fill', '#000')
      .style("font-size", "0.7em")
      .text(function (d, i) {
        return Integer.number_format(d3.sum(d.value), 0, ',', '.');
      })
  }
});