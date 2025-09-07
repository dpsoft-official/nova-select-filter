<template>
  <div>
    <span v-if="displayValue" class="text-sm">{{ displayValue }}</span>
    <span v-else class="text-gray-400 text-sm">—</span>
  </div>
</template>

<script>
export default {
  props: ['field'],

  computed: {
    displayValue() {
      if (!this.field.value && this.field.value !== 0) return '';
      
      let value = this.field.value;
      
      // If it's a string, try to parse as JSON
      if (typeof value === 'string') {
        try {
          value = JSON.parse(value);
        } catch (e) {
          // If JSON parse fails, treat as single value
          return this.getLabelForValue(value);
        }
      }
      
      if (Array.isArray(value)) {
        if (value.length === 0) return '';
        
        const labels = value.map(val => this.getLabelForValue(val));
        return labels.join(', ');
      }
      
      return this.getLabelForValue(value);
    },
  },

  methods: {
    getLabelForValue(value) {
      const options = this.field.options || [];
      const option = options.find(opt => String(opt.value) === String(value));
      return option ? option.label : value;
    },
  },
};
</script>
