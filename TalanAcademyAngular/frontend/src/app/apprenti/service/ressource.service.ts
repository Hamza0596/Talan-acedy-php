import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import {Observable} from "rxjs";
import {environment} from "../../../environments/environment";

@Injectable({
  providedIn: 'root'
})
export class RessourceService {

  apiUrl = environment.apiUrl;
  constructor(private http: HttpClient) { }
  getRessource() :Observable<any>{

    return this.http.get<any>(`${this.apiUrl}/apprentice/resources`) ;
  }

  recommendationResource(data: any , resourceId : number): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/apprentice/resource/recommendation/${resourceId}`, data);
  }

  addRessources(data: any , dayId : number):Observable<any>{
return this.http.post<any>(`${this.apiUrl}/apprentice/resources/${dayId}`, data)
  }
}
