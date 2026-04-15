import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { TextControl } from '@wordpress/components';
// useSelect lets us READ data from the WordPress editor store
// useDispatch lets us WRITE data back to the WordPress editor store
import { useSelect, useDispatch } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';

// Use curly braces {} for the component body so we can declare
// variables before returning the JSX
const EmployeeProfilePanel = () => {
  // Read the current post meta from the editor store
  // meta will be an object like { _employee_job_title: 'Developer', ... }
  const meta = useSelect(select =>
    select(editorStore).getEditedPostAttribute('meta'),
  );

  // Get the editPost function from the editor store
  // We use this to write changes back when the user types in a field
  const { editPost } = useDispatch(editorStore);

  // Return the JSX — this is what renders in the sidebar
  return (
    <PluginDocumentSettingPanel
      name='ag-employee-profiles-panel'
      title='Employee Profile Details'>
      <TextControl
        label='Job Title'
        // Read the current value from meta (use empty string as fallback)
        value={meta?._employee_job_title ?? ''}
        // When the user types, write the new value back to the editor store
        // The editor will include this in the REST API call when the user clicks Update
        onChange={value => editPost({ meta: { _employee_job_title: value } })}
      />
      <TextControl
        label='Employee Company'
        // Read the current value from meta (use empty string as fallback)
        value={meta?._employee_company ?? ''}
        // When the user types, write the new value back to the editor store
        // The editor will include this in the REST API call when the user clicks Update
        onChange={value => editPost({ meta: { _employee_company: value } })}
      />
    </PluginDocumentSettingPanel>
  );
};

registerPlugin('ag-employee-profiles', {
  render: EmployeeProfilePanel,
});
