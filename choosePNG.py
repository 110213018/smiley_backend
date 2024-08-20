# from predict import statistics
import random
import os

def main():
    compare()
    choose(posIndex, negIndex)

def compare():
    statistics = [25, 44, 66, 654, 84]
    emotion = ['sadness', 'disgust', 'like', 'anger', 'happiness', 'other']
    pos = [statistics[2], statistics[4]]
    neg = [statistics[0], statistics[1], statistics[3]]
    # print(pos, neg)
    maxPos = max(pos)
    maxNeg = max(neg)
    # print(maxPos, maxNeg)
    posIndex = pos.index(maxPos)
    negIndex = neg.index(maxNeg)
    # print(posIndex, negIndex)
    return posIndex, negIndex

def range():

def choose(pos, neg):
    source = "C:/xampp/htdocs/smiley_backend/img/angel_monster"
    num = random.randint(1, 4) 
    monster = f'moster_{num}.png'     
    print(monster)


main()