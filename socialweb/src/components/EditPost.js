import React from 'react';
import CreatePost  from './CreatePost';
import {useParams} from 'react-router-dom';

function EditPost() {

	let {idPost} = useParams();

	return (
			<React.Fragment>
				<CreatePost
						idPost={idPost}
				/>
			</React.Fragment>
	);
}

export default EditPost;