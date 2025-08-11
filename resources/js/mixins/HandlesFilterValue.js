export default {
  data() {
    return {
      options: [],
      selectedCache: {},
    };
  },

  beforeMount() {
    this.options = this.filter.options || [];
  },

  methods: {
    getInitialFilterValuesArray() {
      try {
        if (!this.value) return void 0;
        if (Array.isArray(this.value)) return this.value;

        // Attempt to parse the field value
        if (typeof this.value === 'string') {
          let value = this.value;
          while (typeof value === 'string') value = JSON.parse(value);
          if (Array.isArray(value)) return value;
        }

        return void 0;
      } catch (e) {
        return void 0;
      }
    },

    getValueFromOptions(value) {
      const sourceOptions = (this.options && this.options.length ? this.options : this.filter.options) || [];
      if (this.isOptionGroups) {
        const flattened = sourceOptions
          .map(optGroup => (optGroup.values || []).map(values => ({ ...values, group: optGroup.label })))
          .flat();
        const found = flattened.find(opt => String(opt.value) === String(value));
        if (found) return found;
        const cached = this.selectedCache[String(value)];
        if (cached) return cached;
        return { label: String(value), value };
      }
      const found = sourceOptions.find(opt => String(opt.value) === String(value));
      if (found) return found;
      const cached = this.selectedCache[String(value)];
      if (cached) return cached;
      return { label: String(value), value };
    },
  },
  computed: {
    isOptionGroups() {
      const src = (this.options && this.options.length ? this.options : this.filter.options) || [];
      return !!src.find(opt => opt && opt.values && Array.isArray(opt.values));
    },

    isMultiselect() {
      return !this.filter.singleSelect;
    },

    computedOptions() {
      let options = this.options || [];

      if (this.isOptionGroups) {
        const allLabels = options.map(opt => opt.values.map(o => o.label)).flat();
        options = options.map(option => {
          return {
            ...option,
            values: option.values.map(opt => {
              const isDuplicate = allLabels.filter(l => l === opt.label).length > 1;
              return { ...opt, label: isDuplicate ? `${opt.label} (${option.label})` : opt.label };
            }),
          };
        });
      }

      return options;
    },
  },
};
