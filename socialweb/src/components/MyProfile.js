import React, {Component} from 'react';
import {Link, Navigate} from 'react-router-dom';
import axios from 'axios';
import qs from 'qs';
import Global from '../Global';
import Main from './Main';
import "react-responsive-carousel/lib/styles/carousel.min.css"; // requires a loader
import { Carousel } from 'react-responsive-carousel';

class MyProfile extends Component{
	url = Global.url;
	token = localStorage.getItem("token");

	imageRef = React.createRef();

	state = {
		user: [],
		idUser: {},
		ownProfile: false,
		sameUser: false,
		befriended: {},
		friends: [],
		petitions: [],
		image: {},
		images: []
	}

	componentDidMount(){
		var idUser = parseInt(this.props.idUser);
		if(idUser === null || idUser === undefined || isNaN(idUser)){
			this.getIdentity(this.token);
		} else if(idUser && typeof(idUser) === 'number' && (idUser !== null || idUser !== undefined)){
			this.getUser(idUser);
			this.getBefriended(idUser);
		}
		this.sameUser();
		this.getPetitions();
		this.getFriends(idUser);
		this.getImages(idUser);
	}

	getPetitions = () => {
		var config = {
			method: 'post',
			url: this.url+'petitions',
			headers: {'Content-Type':'x-www-form-urlencoded', 'Authorization': this.token.replace(/['"]+/g, '')}
		}

		axios(config)
			.then(res => {
				this.setState({
					petitions: res.data.petitions
				});
			});
	}

	getIdentity = () => {
		var config = {
			method: 'post',
			url: this.url+'identity',
			headers: {'Authorization': this.token.replace(/['"]+/g, '')}
		}

		axios(config)
			.then(res => {
				this.setState({
					user: res.data.user,
					ownProfile: true
				});
			});
	}

	getUser = (idUser) => {
		var data = qs.stringify({
			'json':'{"idUser":'+idUser+'}'
		});

		var config = {
			method: 'post',
			url: this.url+'user',
			headers: {'Content-Type':'application/x-www-form-urlencoded'},
			data: data
		}

		axios(config)
			.then(res => {
				this.setState({
					user: res.data.user,
					idUser: idUser,
					ownProfile: false
				});
			});
	}

	managePetition = (status, idPetition) => (e) => {
		e.preventDefault();
		var data = qs.stringify({
			'json':'{"idPetition":'+idPetition+', "status":'+status+'}'
		});

		var config = {
			method: 'post',
			url: this.url+'manage-petition',
			headers: {'Content-Type': 'application/x-www-form-urlencoded', 'Authorization': this.token.replace(/['"]+/g, '')},
			data: data
		};

		axios(config)
			 .then(res => {
			 	console.log(res.data.petition);
			 });
	}

	befriends = (idUser) => (e) => {
		e.preventDefault();
		var data = qs.stringify({
			'json':'{"target_user":'+idUser+'}'
		});

		var config = {
			method: 'post',
			url: this.url+'befriend',
			headers: {'Content-Type': 'application/x-www-form-urlencoded', 'Authorization': this.token.replace(/['"]+/g, '')},
			data: data
		};

		axios(config)
			 .then(res => {
			 	console.log(res.data.friend);
			 });
	}

	sameUser = () => {
		var config = {
			method: 'post',
			url: this.url+'identity',
			headers: {'Authorization': this.token.replace(/['"]+/g, '')}
		}

		axios(config)
			.then(res => {
				if(res.data.user.id === this.state.user.id && this.state.ownProfile === false){
					this.setState({
						sameUser: true
					});
				}
			});
	}

	getBefriended = (idBeFriended) => {
		var data = qs.stringify({
			'json':'{"user2":'+idBeFriended+'}'
		});

		var config = {
			method: 'post',
			url: this.url+'befriended',
			headers: {'Content-Type': 'application/x-www-form-urlencoded', 'Authorization': this.token.replace(/['"]+/g, '')},
			data: data
		}

		axios(config)
			.then(res => {
				this.setState({
					befriended: res.data.befriended
				})
			});
	}

	setHomeLimit = () => (e) =>{
		e.preventDefault();
		var limite = this.state.limite + 10;
		this.setState({
			limite: this.state.limite+10
		});
		this.home(limite);
	}

	getFriends = (user) => {
		var data = qs.stringify({
			'json':'{"userId":'+user+'}'
		});

		var config = {
			method: 'post',
			url: this.url+'friends',
			headers: {'Content-Type':'application/x-www-form-urlencoded', 'Authorization': this.token.replace(/['"]+/g, '')},
			data: data
		}

		axios(config)
			.then(res => {
				this.setState({
					friends: res.data.friends
				})
			});
	}

	fileChange = (e) => {
		this.setState({
			image: e.target.files[0]
		});
		this.forceUpdate();
	}

	uploadImage = (e) => {
		e.preventDefault();

		if(this.state.image !== null){
			const formData = new FormData();

			formData.append(
				 'filename',
				 	this.state.image,
				 	this.state.image.name
			);

				var ifconfig = {
					method: 'post',
					url: this.url+'upload-image',
					headers: {'Authorization': localStorage.getItem('token').replace(/['"]+/g, '')},
					data: formData
				};

				axios(ifconfig)
				 	.then(res => {
				 		this.setState({
				 			image: this.state.image
				 		});
				});
		}
	}

	getImages = (user) => {
		var data = qs.stringify({
			'json':'{"idUser":'+user+'}'
		});

		var config = {
			method: 'post',
			url: this.url+'user-images',
			headers: {'Content-Type':'application/x-www-form-urlencoded', 'Authorization': this.token.replace(/['"]+/g, '')},
			data: data
		}

		axios(config)
			.then(res => {
				this.setState({
					images: res.data.images
				})
			});
	}

	changeProfileImage = (image) => (e) => {
		e.preventDefault();
		var data = qs.stringify({
			'json':'{"image":"'+image+'"}'
		});

		var config = {
			method: 'put',
			url: this.url+'change-profile-image',
			headers: {'Content-Type':'application/x-www-form-urlencoded', 'Authorization': this.token.replace(/['"]+/g, '')},
			data: data
		}

		axios(config)
			.then(res => {
				this.setState({
					user: res.data.user
				})
			});
	}

	deleteImage = (idImage) => (e) => {
		e.preventDefault();
		var config = {
			method: 'delete',
			url: this.url+'delete-image/'+idImage,
			headers: {'Content-Type':'application/x-www-form-urlencoded', 'Authorization': localStorage.getItem('token').replace(/['"]+/g, '')}
		}

		axios(config)
			.then(res => {
				console.log(res.data);
			});
	}

	render(){
		console.log(this.state.friends);
		console.log(this.state.user.id);
		if(this.state.user.nombre){
			return(
				<div className="center">
					{this.state.sameUser === true &&
						<Navigate to="/my-profile" replace={true} />
					}
					<div className="profile">
						<div className="friends">
							<h3>Amigos</h3>
							<div className="friends-box">
								{this.state.friends.length >= 1 ? (
									this.state.friends.map((friend) => {
										return(
											<div className="friend">				
												<img className="avatar" src={this.url+'foto/'+friend.image} alt={friend.image} />
												<h4><Link replace to={"/user-profile/"+friend.id}>{friend.nombre} {friend.apellidos}</Link></h4>
											</div>
										)
									})) : (
										<h4>No tienes amigos agregados aún</h4>
								)}
							</div>
						</div>
						<div className="profileUser">
							<h2>{this.state.user.nombre} {this.state.user.apellidos}</h2>
							{this.state.sameUser !== true && this.state.ownProfile !== true && this.state.befriended === 3 &&
								<button onClick={this.befriends(this.state.idUser)}>Añadir amigo</button>
							}
							{this.state.sameUser !== true && this.state.ownProfile !== true && this.state.befriended === 2 &&
								<button disabled>Ya le has enviado petición</button>
							}
							{this.state.sameUser !== true && this.state.ownProfile !== true && this.state.befriended === 1 &&
								<button disabled>Ya sois amigos</button>
							}
							<img className="imageProfile" src={this.url+'foto/'+this.state.user.image} alt={this.state.user.image} />
						</div>
						<aside className="profile">
							{this.state.ownProfile === true &&
							<div className="petitions">
								<h4>Peticiones de amistad:</h4>
								{this.state.petitions.length >= 1 ? (
								this.state.petitions.map((petition) => {
									return(
										<React.Fragment>
											<h5>{petition.user1.nombre} {petition.user1.apellidos}</h5>
											<button onClick={this.managePetition(1, petition.id)}>Aceptar</button>
											<button onClick={this.managePetition(0, petition.id)}>Rechazar</button>
										</React.Fragment>
									)
								})) : (
										<h4>No tienes peticiones en este momento</h4>
								)}
							</div>
							}
							<h4 id="createPost"><Link replace to="/create-post">Crear post</Link></h4>
						</aside>
					</div>
					<div className="fotos-box">
						{this.state.images.length >= 1 ? (
							this.state.images.map((image) => {
								return(
								<div className="image-box">
									<img className="userImage" src={this.url+'foto/'+image.filename} alt={image.filename} />
									<button onClick={this.changeProfileImage(image.filename)}>Establecer como foto de perfil</button>
									<button onClick={this.deleteImage(image.id)}>Borrar</button>
								</div>
								)
							})) : (
							<h2>No tienes imágenes aún</h2>
						)}
					</div>
					<form onSubmit={this.uploadImage}>
						<input type="file" name="file0" ref={this.imageRef} onChange={this.fileChange} />
						<input type="submit" value="Subir imagen" />
					</form>
					{this.state.ownProfile === false ? (
						<Main
						userId={this.state.idUser}
						/>
					) : (
						<Main
						userId={this.state.user.id}
						/>
					)}

				</div>
			);
		}else {
			return(
				<h2>Cargando...</h2>
			);
		}
	}
}
export default MyProfile;