<template>
  <panel-item :field="field">
    <template slot="value">
      <div v-if="displayValue" class="flex flex-wrap">
        <span 
          v-for="(label, index) in displayLabels" 
          :key="index"
          class="inline-block bg-primary-500 text-white text-xs px-2 py-1 rounded mr-1 mb-1"
        >
          {{ label }}
        </span>
      </div>
      <span v-else class="text-gray-400">—</span>
    </template>
  </panel-item>
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
          return value;
        }
      }
      
      return value;
    },

    displayLabels() {
      if (!this.displayValue && this.displayValue !== 0) return [];
      
      const value = this.displayValue;
      const options = this.field.options || [];
      
      if (Array.isArray(value)) {
        return value.map(val => {
          const option = options.find(opt => String(opt.value) === String(val));
          return option ? option.label : val;
        });
      }
      
      const option = options.find(opt => String(opt.value) === String(value));
      return [option ? option.label : value];
    },
  },
};
</script>
