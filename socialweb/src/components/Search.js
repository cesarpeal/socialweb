import React from 'react';
import Main  from './Main';
import {useParams} from 'react-router-dom';

function Search() {

	let {search} = useParams();

	return (
			<React.Fragment>
				<h2 className="subtitle">Búsqueda: {search}</h2>
				<Main
						search={search}
				/>
			</React.Fragment>
	);
}

export default Search;