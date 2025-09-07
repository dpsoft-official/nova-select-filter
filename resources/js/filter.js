import MultiselectFilter from './components/MultiselectFilter';
import IndexField from './components/IndexField';
import DetailField from './components/DetailField';
import FormField from './components/FormField';

Nova.booting((Vue, router, store) => {
  Vue.component('nova-multiselect-filter', MultiselectFilter);
  Vue.component('index-nova-multiselect-field', IndexField);
  Vue.component('detail-nova-multiselect-field', DetailField);
  Vue.component('form-nova-multiselect-field', FormField);
});
