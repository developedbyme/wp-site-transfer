"use strict";

import React from "react";
import ReactDOM from "react-dom";

// import SyncTestNotice from "oddsitetransfer/admin/sync/SyncTestNotice";
export default class SyncTestNotice extends React.Component {

	constructor(props) {
		super(props);
		
		this.state = {
			
		};
		
		
	}
	
	render() {
		
		if(this.props.status === "connected") {
			
			var info = this.props.info;
			
			return <p>
				Connected to site running version {info.version}
			</p>;
		}
		
		return <p>
			Not connected (Http code: {this.props.httpCode}, Data: {this.props.loadedData});
		</p>;
		
	}
}