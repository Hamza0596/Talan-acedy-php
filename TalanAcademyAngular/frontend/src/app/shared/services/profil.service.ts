import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from 'src/environments/environment';
import { Apprenti } from '../models/apprenti';

@Injectable({
  providedIn: 'root'
})
export class ProfilService {

  apiUrl = environment.apiUrl;
  constructor(private http: HttpClient) {}

  getImage(){
    return this.http.get(`${this.apiUrl}/apprentice/profile/image`,  { responseType: 'blob' });
  }

  changeImage(image :FormData){
    return this.http.post(`${this.apiUrl}/apprentice/profile/image`, image);
  }

  updateApprentiProfil(apprenti: Apprenti){
    return this.http.patch(`${this.apiUrl}/apprentice/profile`, apprenti);
  }

  updateApprentiPassword(passwords: any){
    return this.http.patch(`${this.apiUrl}/apprentice/profile/password`, passwords);
  }


}
