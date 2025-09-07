<template>
  <default-field :field="field" :errors="errors" :show-help-text="!isReadonly && showHelpText">
    <template slot="field">
      <multiselect
        @input="handleChange"
        @search-change="handleSearchChange"
        track-by="value"
        label="label"
        :group-label="isOptionGroups ? 'label' : void 0"
        :group-values="isOptionGroups ? 'values' : void 0"
        :group-select="field.groupSelect || false"
        ref="multiselect"
        :value="selected"
        :options="computedOptions"
        :placeholder="field.placeholder || field.name"
        :close-on-select="field.max === 1"
        :clear-on-select="false"
        :multiple="isMultiselect"
        :max="field.max || null"
        :optionsLimit="field.optionsLimit || 1000"
        :optionHeight="32"
        :internal-search="!isAjax"
        :loading="isLoading"
        :limitText="count => __('novaMultiselectFilter.limitText', { count: String(count || '') })"
        selectLabel=""
        selectGroupLabel=""
        selectedLabel=""
        deselectLabel=""
        deselectGroupLabel=""
      >
        <template slot="maxElements" style="color: #7c858e;font-size: 13px;">
          <span style="color: tomato;">{{ __('novaMultiselectFilter.maxElements', { max: String(field.max || '') }) }}</span>
        </template>

        <template slot="noResult" >
          <span style="color: #7c858e;font-size: 13px;">{{ __('novaMultiselectFilter.noResult') }}</span>
        </template>

        <template slot="noOptions" style="color: tomato;">
          <span style="color: #7c858e;font-size: 13px;">{{ __('novaMultiselectFilter.noOptions') }}</span>
        </template>
      </multiselect>
    </template>
  </default-field>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova';
import HandlesFieldValue from '../mixins/HandlesFieldValue';
import Multiselect from 'vue-multiselect';

export default {
  mixins: [FormField, HandlesValidationErrors, HandlesFieldValue],
  components: { Multiselect },

  data: () => ({
    options: [],
    selectedOptions: [],
    isLoading: false,
    debounceTimer: null,
  }),

  mounted() {
    this.options = this.field.options || [];
    this.initializeValue();
  },

  methods: {
    handleChange(value) {
      if (!this.isMultiselect) {
        this.value = value ? value.value : null;
        this.selectedOptions = value ? [value] : [];
      } else {
        const values = value ? value.map(v => v.value) : [];
        this.value = values;
        this.selectedOptions = value || [];
      }

      if (Array.isArray(value)) {
        value.forEach(v => {
          if (v && v.value != null) this.selectedCache[String(v.value)] = v;
        });
      }
    },

    initializeValue() {
      if (this.field.value !== null && this.field.value !== undefined) {
        let fieldValue = this.field.value;
        
        // If it's a string, try to parse as JSON
        if (typeof fieldValue === 'string') {
          try {
            fieldValue = JSON.parse(fieldValue);
          } catch (e) {
            // If JSON parse fails, treat as single value
            if (this.isMultiselect) {
              fieldValue = [fieldValue];
            }
          }
        }

        if (this.isMultiselect) {
          // For multiselect, ensure we have an array
          if (Array.isArray(fieldValue)) {
            this.value = fieldValue;
            this.selectedOptions = fieldValue.map(this.getValueFromOptions).filter(Boolean);
          } else {
            // Single value in multiselect mode, wrap in array
            this.value = fieldValue !== null ? [fieldValue] : [];
            this.selectedOptions = fieldValue !== null ? [this.getValueFromOptions(fieldValue)].filter(Boolean) : [];
          }
        } else {
          // For single select, extract single value
          if (Array.isArray(fieldValue)) {
            this.value = fieldValue.length > 0 ? fieldValue[0] : null;
            this.selectedOptions = fieldValue.length > 0 ? [this.getValueFromOptions(fieldValue[0])].filter(Boolean) : [];
          } else {
            this.value = fieldValue;
            this.selectedOptions = fieldValue !== null ? [this.getValueFromOptions(fieldValue)].filter(Boolean) : [];
          }
        }
      } else {
        // Initialize with empty values
        this.value = this.isMultiselect ? [] : null;
        this.selectedOptions = [];
      }
    },

    handleSearchChange(query) {
      if (!this.isAjax) return;
      const minChars = this.field.minChars || 0;
      if (this.debounceTimer) clearTimeout(this.debounceTimer);
      if (!query || String(query).length < minChars) {
        this.options = [];
        this.isLoading = false;
        return;
      }
      this.isLoading = true;
      const delay = this.field.debounce || 300;
      const method = (this.field.ajaxMethod || 'get').toLowerCase();
      const endpoint = this.field.ajaxEndpoint || '/nova-vendor/nova-multiselect-filter/search';
      const paramKey = this.field.ajaxParam || 'search';
      this.debounceTimer = setTimeout(async () => {
        try {
          const params = {};
          params[paramKey] = query;
          if (this.field.model) params.model = this.field.model;
          if (this.field.searchColumn) params.column = this.field.searchColumn;
          let response;
          if (method === 'post') {
            response = await Nova.request().post(endpoint, params);
          } else {
            response = await Nova.request().get(endpoint, { params });
          }
          const data = response && response.data ? response.data : [];
          const options = Array.isArray(data) ? data : (data && Array.isArray(data.options) ? data.options : []);
          this.options = options || [];
        } catch (e) {
          this.options = [];
        } finally {
          this.isLoading = false;
        }
      }, delay);
    },

    fill(formData) {
      if (this.isMultiselect) {
        // For multiselect, always send as JSON array
        formData.append(this.field.attribute, JSON.stringify(this.value || []));
      } else {
        // For single select, send the single value directly
        formData.append(this.field.attribute, this.value || '');
      }
    },
  },

  computed: {
    selected() {
      return this.selectedOptions;
    },

    isAjax() {
      return !!(this.field && (this.field.ajaxEndpoint || this.field.model));
    },
  },
};
</script>

<style>
@import '~vue-multiselect/dist/vue-multiselect.min.css';
.multiselect{
  text-align: right;
}
.multiselect__tags{
  padding: 8px 8px 0 40px;
  font-size: 13px;
}
.multiselect__tag{
  padding: 4px 10px 4px 26px;
  margin-left: 10px;
  margin-right: unset;
  background-color: #8A018A;
}
.multiselect__tag-icon{
  right:unset;
  left:0;
  margin-left: 0px;
}
.multiselect__spinner{
  right:unset;
  left:1;
}
.multiselect__select{
  right:unset;
  left:1;
}
.multiselect__select:before{
  color:black;
  border-color: #090909 transparent transparent;
}
.multiselect__option--highlight,.multiselect__option--highlight:after{
  background-color: #8A018A;
}
.multiselect__spinner:after,.multiselect__spinner:before{
  border-top-color: #8A018A;
}
.multiselect__tag-icon:focus,.multiselect__tag-icon:hover {
    background:rgb(244, 35, 244);
}
.multiselect__tag-icon:after {
    color:rgb(248, 244, 248);
}
.multiselect__element{
  font-size: 12px;
}
</style>
