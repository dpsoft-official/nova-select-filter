<template>
  <div class="nova-multiselect-filter">
    <h3 class="text-sm uppercase tracking-wide text-80 bg-30 p-3">
      {{ filter.name }}
    </h3>

    <div class="p-2 flex relative">
      <multiselect
        @input="handleChange"
        @close="handleClose"
        @remove="handleRemove"
        @open="handleOpen"
        @search-change="handleSearchChange"
        track-by="value"
        label="label"
        :group-label="isOptionGroups ? 'label' : void 0"
        :group-values="isOptionGroups ? 'values' : void 0"
        :group-select="filter.groupSelect || false"
        ref="multiselect"
        :value="selected"
        :options="computedOptions"
        :placeholder="filter.placeholder || filter.name"
        :close-on-select="filter.max === 1"
        :clear-on-select="false"
        :multiple="isMultiselect"
        :max="filter.max || null"
        :optionsLimit="filter.optionsLimit || 1000"
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
        <template slot="maxElements">
          {{ __('novaMultiselectFilter.maxElements', { max: String(filter.max || '') }) }}
        </template>

        <template slot="noResult">
          {{ __('novaMultiselectFilter.noResult') }}
        </template>

        <template slot="noOptions">
          {{ __('novaMultiselectFilter.noOptions') }}
        </template>
      </multiselect>
    </div>
  </div>
</template>

<script>
import HandlesFilterValue from '../mixins/HandlesFilterValue';
import Multiselect from 'vue-multiselect';
import { Filterable, InteractsWithQueryString } from 'laravel-nova';

export default {
  components: { Multiselect },
  mixins: [Filterable, InteractsWithQueryString, HandlesFilterValue],
  props: ['resourceName', 'resourceId', 'filterKey'],

  data: () => ({
    options: [],
    isDropdownOpen: false,
    selectedOptions: [],
    isTouched: false,
    isLoading: false,
    debounceTimer: null,
  }),

  methods: {
    handleChange(value) {
      if (!this.isMultiselect) value = value ? [value] : [];
      this.isTouched = true;
      this.selectedOptions = value;
      if (Array.isArray(value)) {
        value.forEach(v => {
          if (v && v.value != null) this.selectedCache[String(v.value)] = v;
        });
      }
    },

    handleClose() {
      this.isDropdownOpen = false;
      this.emitChanges();
    },

    handleOpen() {
      this.isDropdownOpen = true;
    },

    handleRemove() {
      // Resolve issue where handleRemove is called before handleChange
      this.$nextTick(() => {
        if (!this.isDropdownOpen) this.emitChanges();
      });
    },

    emitChanges() {
      //  Check if values have been changed
      if (JSON.stringify(this.value) === JSON.stringify(this.values) || this.values == null) return;

      // Update filter state
      this.$store.commit(`${this.resourceName}/updateFilterState`, {
        filterClass: this.filterKey,
        value: this.values,
      });

      // Reset selected options and is touched
      this.isTouched = false;
      this.$emit('change');
    },

    handleSearchChange(query) {
      if (!this.isAjax) return;
      const minChars = this.filter.minChars || 0;
      if (this.debounceTimer) clearTimeout(this.debounceTimer);
      if (!query || String(query).length < minChars) {
        this.options = [];
        this.isLoading = false;
        return;
      }
      this.isLoading = true;
      const delay = this.filter.debounce || 300;
      const method = (this.filter.ajaxMethod || 'get').toLowerCase();
      const endpoint = this.filter.ajaxEndpoint || '/nova-vendor/nova-multiselect-filter/search';
      const paramKey = this.filter.ajaxParam || 'search';
      this.debounceTimer = setTimeout(async () => {
        try {
          const params = {};
          params[paramKey] = query;
          if (this.filter.model) params.model = this.filter.model;
          if (this.filter.searchColumn) params.column = this.filter.searchColumn;
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
  },

  computed: {
    filter() {
      return this.$store.getters[`${this.resourceName}/getFilter`](this.filterKey);
    },

    value() {
      return this.filter.currentValue;
    },

    selected() {
      // If modified, return modified array
      if (this.isTouched) return this.selectedOptions;

      // Else return from $store
      const valuesArray = this.getInitialFilterValuesArray();
      return valuesArray && valuesArray.length ? valuesArray.map(this.getValueFromOptions).filter(Boolean) : [];
    },

    values() {
      if (!this.isTouched) return null;
      const values = [];

      // Return only values for query
      this.selectedOptions.forEach(option => {
        values.push(option.value);
      });

      return values.length ? values : '';
    },

    isAjax() {
      return !!(this.filter && (this.filter.ajaxEndpoint || this.filter.model));
    },
  },
};
</script>

<style >
@import '~vue-multiselect/dist/vue-multiselect.min.css';
.multiselect{
  text-align: right;
}
.multiselect__tags{
  padding: 8px 8px 0 40px;
}
.multiselect__tag{
  padding: 4px 10px 4px 26px;
  margin-left: 10px;
  margin-right: unset;
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
</style>