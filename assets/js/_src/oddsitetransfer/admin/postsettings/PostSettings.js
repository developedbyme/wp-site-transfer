"use strict";

import React from "react";
import ReactDOM from "react-dom";

// import PostSettings from "oddsitetransfer/admin/postsettings/PostSettings";
export default class PostSettings extends React.Component {

	constructor(props) {
		super(props);
		
		this.state = new Object();
		this.state['change'] = false;
		
		this._callback_toggleSettingsBound = this._callback_toggleSettings.bind(this);
	}
	
	_callback_toggleSettings(aEvent) {
		var newValue = !this.state['change'];
		
		this.setState({'change': newValue});
	}
	
	componentDidMount() {
		
	}
	
	render() {
		
		var changeText = (!this.state['change']) ? "Change settings" : "Cancel changes";
		
		var formFields = null;
		if(this.state['change']) {
			
			var defaultValue = "";
			if(this.props.metaFields && this.props.metaFields["_odd_server_transfer_id"]) {
				defaultValue = this.props.metaFields["_odd_server_transfer_id"];
			}
			
			formFields = <div>
				<label htmlFor="_odd_server_transfer_id">Transfer id:</label>
				<input type="text" name="_odd_server_transfer_id" defaultValue={defaultValue} style={{"width": 350}} />
			</div>;
		}
		
		return <div>
			<div>
				<span className="button button-primary button-large" onClick={this._callback_toggleSettingsBound}>{changeText}</span>
				<span style={{"lineHeight": "28px", "marginLeft": "10px"}}><strong>Warning:</strong> changing these settings might cause unexpected behaviour</span>
			</div>
			{formFields}
		</div>;
		
	}
}