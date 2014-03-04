Email Tracking And Filtering Front End:
---------------------------------------

I spoke with Kevin, and here's the known differences/issues/things to work on (in no particular order):
- The date range boxes in the current copy of the .php search is using a built in HTML5 date/time picker. Because of lack of browser support, Kevin and I decided we'll just go with a datepicker and not pass time at all into the search parameter. The two <inputs> are type="text", and are named start_date and end_date. There will need to be some sort of function/method that takes the input and parses it. It should be pretty easy, since it's in the MM/DD/YYYY and the /'s separate the fields.
- The netID box isn't in the UI because it presents confusion (since it doesn't apply to all three). FYI.
- In my HTML version of it, there are three checkboxes in the HTML with name="server" to filter by server. I'm not sure if you want to leave those out and incorporate those later, but just a heads up.
- In the table I created, we left off the left side counter column, the incident ID column (since it's usually blank), and the "view logs" button; I planned on creating a client-side script that makes an AJAX call to get the log data (I'll need your help with that). Assuming the information that is required to make that call can be stuck in a hidden field somehwere at some point, I don't think we need it for now?
- In my table, there are separate columns for date & time - Kevin said we could parse it so those end up in different columns, rather than in a single column.
- The column entitled "what" has been changed to be titled "Status"
- Be aware of the top of the table -> <tr class="table-information"><td colspan="8">Routers Results</td></tr>
- I tried to go through the inputs and rename them to match the names of your .php form.
- I'll add a logout/BYU search button at the top at some point.
- Let me know if you notice any browser quirks, as far as styling goes. Firefox has a known issue with the dropdowns, but the rest should be fine.

Let me know if you have any questions.
- Trevor