import React, {Component} from 'react';
import axios from 'axios';
import qs from 'qs';
import Global from '../Global';
import "react-responsive-carousel/lib/styles/carousel.min.css"; // requires a loader
import { Carousel } from 'react-responsive-carousel';

class CreatePost extends Component{
	url = Global.url;
	token = localStorage.getItem('token');

	tituloRef = React.createRef();
	contenidoRef = React.createRef();
	imageRef = React.createRef();

	state = {
		post: {},
		foto_post: null,
		idPost: null,
		fotos_post: null
	}

	componentDidMount(){
		var idPost = this.props.idPost;
		if(idPost && (idPost !== null || idPost !== undefined)){
			this.getPost(idPost);
			this.setState({
				idPost: idPost
			});
		}
	}

	handleChange = () => {
		this.setState({
			post: {
				titulo: this.tituloRef.current.value,
				contenido: this.contenidoRef.current.value
			}
		});
		this.forceUpdate();
	}

	fileChange = (e) => {
		this.setState({
			foto_post: e.target.files[0]
		});
		this.forceUpdate();
	}

	handleSubmit = (e) => {
		e.preventDefault();
		this.handleChange();

		var data = qs.stringify({
			'json':'{"titulo":"'+this.state.post.titulo+'", "contenido":"'+this.state.post.contenido+'"}'
		});



		if(this.state.idPost && this.state.idPost !== null){
			var config = {
					method: 'put',
					url: this.url+'editar-post/'+this.state.idPost,
					headers: {'Content-Type':'application/x-www-form-urlencoded', 'Authorization':this.token.replace(/['"]+/g, '')},
					data: data
			}
		} else {
			var config = {
				method: 'post',
				url: this.url+'crear-post',
				headers: {'Content-Type':'application/x-www-form-urlencoded', 'Authorization':this.token.replace(/['"]+/g, '')},
				data: data
			}
		}

		axios(config)
			.then(res => {
				this.setState({
					post: res.data.post
				});

			if(this.state.foto_post !== null){
				const formData = new FormData();

				formData.append(
				 	'filename',
					 	this.state.foto_post,
					 	this.state.foto_post.name
				);

					var ifconfig = {
						method: 'post',
						url: this.url+'upload-post-image/'+res.data.post.id,
						headers: {'Authorization': localStorage.getItem('token').replace(/['"]+/g, '')},
						data: formData
					};

					axios(ifconfig)
					 	.then(res => {
					 		this.setState({
					 			foto_post: this.state.foto_post
					 		});
					});
			}
		});
	}

	getPost = (id) => {
		var data = qs.stringify({
			'json':'{"id":'+id+'}'
		});

		var config = {
			method: 'post',
			url: this.url+'post',
			headers: {'Content-Type':'application/x-www-form-urlencoded'},
			data: data
		}

		axios(config)
			.then(res => {
				this.setState({
					post: res.data.post,
					fotos_post: res.data.fotos_post
				});
			});
	}

	deleteFotoPost = (idPost) => (e) => {
		e.preventDefault();
		var config = {
			method: 'delete',
			url: this.url+'delete-post-image/'+idPost,
			headers: {'Content-Type':'application/x-www-form-urlencoded', 'Authorization': localStorage.getItem('token').replace(/['"]+/g, '')}
		}

		axios(config)
			.then(res => {
				console.log(res.data);
			});
	}


	render(){
		return(
			<form className="form" onSubmit={this.handleSubmit}>
				<div className="form-group">
					<label htmlFor="titulo">Título del post</label>
					<input type="text" name="titulo" defaultValue={this.state.post.titulo} ref={this.tituloRef} onChange={this.handleChange} />
				</div>
				<div className="form-group">
					<label htmlFor="contenido">Contenido del post</label>
					<textarea name="contenido" cols="50" rows="2" defaultValue={this.state.post.contenido} ref={this.contenidoRef} onChange={this.handleChange}></textarea>
				</div>

				<div className="form-group-image">
					<label htmlFor="image">Imagen</label>
					<input type="file" name="file0" ref={this.imageRef} onChange={this.fileChange} />
					{this.state.fotos_post !== null &&
						<Carousel
						showArrows={true}
						infiniteLoop={true}
						showThumbs={false}
						showIndicators={false}
						>
							{this.state.fotos_post.map((foto_post) => {
								return(
									<React.Fragment>
										<img className="imageCreatePost" src={this.url+'foto-post/'+foto_post.filename} alt={foto_post.filename} />
										<button onClick={this.deleteFotoPost(foto_post.id)}>Borrar foto</button>
									</React.Fragment>
								);
							})}
						</Carousel>
					}
				</div>

				<div className="form-group">
					{(this.state.idPost !== null) ? (
						<input type="submit" value="Editar post" />
						) : (
						<input type="submit" value="Crear post" />
					)}
				</div>
			</form>
		);
	}
}
export default CreatePost;