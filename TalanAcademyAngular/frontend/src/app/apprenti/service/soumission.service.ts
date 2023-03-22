import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import {Observable} from "rxjs";
import {environment} from "../../../environments/environment";

@Injectable({
  providedIn: 'root'
})
export class SoumissionService {
  apiUrl = environment.apiUrl;
  constructor(private http: HttpClient) { }
  submitGitLink (dayId : number,submissionForm :any) :Observable<any>{
    return this.http.post(`${this.apiUrl}/apprentice/submission/${dayId}` , submissionForm) ;

  }
}
