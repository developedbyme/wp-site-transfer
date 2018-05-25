import React from "react";

import WprrBaseObject from "wprr/WprrBaseObject";

import OpenCloseExpandableArea from "wprr/elements/area/OpenCloseExpandableArea";
import EditableProps from "wprr/manipulation/EditableProps";
import SetValueButton from "wprr/elements/interaction/SetValueButton";
import WprrDataLoader from "wprr/manipulation/loader/WprrDataLoader";

import SyncTransfers from "oddsitetransfer/SyncTransfers";

//import GlobalSyncModule from "oddsitetransfer/GlobalSyncModule";
export default class GlobalSyncModule extends WprrBaseObject {
	constructor(props) {
		super(props);
	}
	
	_renderMainElement() {
		return <wrapper>
			<div className="global-sync-notice">
				<EditableProps editableProps="open" open={false}>
					<OpenCloseExpandableArea>
						<div className="global-sync-notice-box">
							<div className="global-sync-notice-box-padding">
								<WprrDataLoader loadData={{"transfers": "wprr/v1/range/ost_transfer/transfers-to-send/transfer"}}>
									<SyncTransfers />
								</WprrDataLoader>
								<SetValueButton valueName="open" value={false}>
									<div>Close</div>
								</SetValueButton>
							</div>
						</div>
					</OpenCloseExpandableArea>
				</EditableProps>
			</div>
		</wrapper>
	}
}