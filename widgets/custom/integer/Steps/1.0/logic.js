RightNow.namespace('Custom.Widgets.integer.Steps');

Custom.Widgets.integer.Steps = RightNow.Widgets.extend({
    /**
     * Constructor del widget
     */
    constructor: function() {
      // Variables
      window.steps              = this;
      this.widget               = this.Y.one(this.baseSelector);
      this.arr_steps            = [];
      this.actual_step_position = 0;
      this.max_steps            = 0;
      this.is_min_step          = 0;
      this.is_max_step          = 0;

      // Mapeo de DOM
      this.steps_container = this.widget.one('.steps');
      this.n_template      = this.steps_container.one('li.template');
      this.steps_groups    = this.Y.all('.rn_StepGroup');
      this.recalculateList();

      // Eventos
      RightNow.Event.subscribe('evt_PrevStep',   this.prevStep,   this);
      RightNow.Event.subscribe('evt_NextStep',   this.nextStep,   this);
      RightNow.Event.subscribe('evt_ChangeStep', this.changeStep, this);
      RightNow.Event.subscribe('evt_AddStep',    this.addStep,    this);
      RightNow.Event.subscribe('evt_DeleteStep', this.deleteStep, this);
      RightNow.Event.subscribe('evt_EnableJump', this.enableJump, this);
    },

    /**
     * Recalcula el arreglo de etapas
     */
    recalculateList: function() {
      this.arr_lists = this.widget.all('.steps li.step');
    },

    /**
     * Permite cambiar al estado anterior
     */
    prevStep: function(e, paramEvt) {
      this.arr_lists.removeClass('active');

      if(this.actual_step_position > 1) {
        this.changeStep(this.actual_step_position - 1);
        this.steps_groups.hide();
        this.Y.all('.rn_Step' + this.actual_step_position).show();

        return true;
      }
      
      return false;
    },

    /**
     * Permite cambiar al estado siguiente
     */
    nextStep: function(e, paramEvt) {
      this.arr_lists.removeClass('active');

      if(this.actual_step_position < this.arr_lists.size() + 1) {
        this.changeStep(this.actual_step_position + 1);
        this.steps_groups.hide();
        this.Y.all('.rn_Step' + this.actual_step_position).show();

        return true;
      }
      
      return false;
    },

    /**
     * Determina si se habilita la etapa para saltar de paso
     */
    enableJump: function(e, paramEvt) {
      config = (typeof e === 'number')?{ "index":e, "enabled": true }:paramEvt[0];

      if(config) {
        if(config.enabled){
          this.arr_lists.item(config.index).addClass('enabled');
        } else {
          this.arr_lists.item(config.index).removeClass('enabled');
        }

        return true;
      }

      return false;
    },

    /**
     * Permite cambiar de estado
     */
    changeStep: function(e, paramEvt) {
      config = (typeof e === 'number')?{ index:e }:paramEvt[0];

      if(config.hasOwnProperty('validate')) {
        if(!this.arr_lists.item(config.index).hasClass('enabled')) {
          return false;
        }
      }

      this.arr_lists.removeClass('active');

      if(config) {
        if(!config.hasOwnProperty('index')) {
          config.index = config;
        }
        
        this.arr_lists.item(config.index).addClass('active');
        this.actual_step_position = config.index;

        this.steps_groups.hide();
        this.Y.all('.rn_Step' + this.actual_step_position).show();

        return true;
      }

      return false;
    },

    /**
     * Agrega una nueva etapa
     */
    addStep: function(e, paramEvt) {
      this.new_step = this.Y.Node.create(this.n_template.get('outerHTML'));
      config        = paramEvt[0];
      
      if(config) {
        if(!config.hasOwnProperty('position')) {
          config.position = this.widget.all('.steps li.step').size();
        }
        
        this.new_step.one('.number').setHTML(config.position);
        if(config.hasOwnProperty('description')) {
          this.new_step.all('.name').setHTML(config.description);
        }
      }
      
      this.steps_container.appendChild(this.new_step);
      this.new_step.show();

      this.new_step.on('click', function(e){
        index = parseInt(e.currentTarget.one('.number').get('text'));
        RightNow.Event.fire('evt_ChangeStep', {"index": index, "validate": true});
      });
      
      this.arr_steps.push(this.new_step);

      this.recalculateList();
      
      return true;
    },
    
    /**
     * Elimina una etapa
     */
    deleteStep: function(e, paramEvt) {
      config = paramEvt[0];

      if(config) {
        if(!config.hasOwnProperty('index')) {
          config.index = config;
        }

        window.steps.arr_steps[config.index];

        this.recalculateList();

        return true;
      }

      return false;
    }
});
