import React from 'react';
import MyProfile  from './MyProfile';
import {useParams} from 'react-router-dom';

function UserProfile() {

	let {idUser} = useParams();

	return (
			<React.Fragment>
				<MyProfile
						idUser={idUser}
				/>
			</React.Fragment>
	);
}

export default UserProfile;