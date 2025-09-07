export default {
  data() {
    return {
      options: [],
      selectedCache: {},
    };
  },

  beforeMount() {
    this.options = this.field.options || [];
  },

  methods: {
    getValueFromOptions(value) {
      const sourceOptions = (this.options && this.options.length ? this.options : this.field.options) || [];
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
      const src = (this.options && this.options.length ? this.options : this.field.options) || [];
      return !!src.find(opt => opt && opt.values && Array.isArray(opt.values));
    },

    isMultiselect() {
      return !this.field.singleSelect;
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
